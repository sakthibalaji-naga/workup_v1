<?php
/**
 * Helper class to log API requests and responses to the database
 */
class API_Logger {

    private static $instance = null;
    private $CI = null;

    private $logTable = 'tbl_api_logs';
    private $tableEnsured = false;

    private function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->database();
        $this->CI->load->dbforge();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new API_Logger();
        }
        return self::$instance;
    }

    /**
     * Log an API request and its response
     *
     * @param string $endpoint - The API endpoint called
     * @param string $method - HTTP method (GET, POST, etc.)
     * @param string $api_key - API key used for authentication
     * @param array $request_data - Request parameters/data
     * @param mixed $response_data - Response from the API call
     * @param int $status_code - HTTP status code of the response
     * @return bool - True on success, false on failure
     */
    public function log_request($endpoint, $method, $api_key, $request_data = [], $response_data = null, $status_code = 200) {
        try {
            $this->ensureLogTableExists();

            // Convert request data to JSON string if it's an array
            $request_body = '';
            if (is_array($request_data) || is_object($request_data)) {
                $request_body = json_encode($request_data);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $request_body = print_r($request_data, true);
                }
            } elseif (is_string($request_data)) {
                $request_body = $request_data;
            }

            // Convert response data to JSON string if needed
            $response_body = '';
            if (is_array($response_data) || is_object($response_data)) {
                $response_body = json_encode($response_data);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $response_body = print_r($response_data, true);
                }
            } elseif (is_string($response_data)) {
                $response_body = $response_data;
            } elseif (is_bool($response_data)) {
                $response_body = $response_data ? 'true' : 'false';
            } else {
                $response_body = (string) $response_data;
            }

            // Get username if API key is provided
            $username_val = null;
            if (!empty($api_key)) {
                $this->CI->db->select('username');
                $this->CI->db->from('tbl_api_users');
                $this->CI->db->where('api_key', $api_key);
                $user_row = $this->CI->db->get()->row();
                if ($user_row && !empty($user_row->username)) {
                    $username_val = $user_row->username;
                }
            }

            // Insert log record
            $log_data = [
                'endpoint' => $endpoint,
                'method' => strtoupper($method),
                'api_key' => $api_key,
                'username' => $username_val,
                'request_body' => $request_body,
                'response_body' => $response_body,
                'status_code' => (int)$status_code,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $result = $this->CI->db->insert($this->logTable, $log_data);

            if (!$result) {
                log_message('error', 'Failed to insert API log: ' . $this->CI->db->error()['message']);
                return false;
            }

            return true;

        } catch (Exception $e) {
            log_message('error', 'Exception in API logging: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Ensure tbl_api_logs exists (auto-create if missing)
     */
    private function ensureLogTableExists() {
        if ($this->tableEnsured) {
            return;
        }

        $tableExists = $this->CI->db->table_exists($this->logTable);

        if (!$tableExists) {
            $fields = [
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'endpoint' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => false,
                ],
                'method' => [
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                    'null' => false,
                ],
                'api_key' => [
                    'type' => 'VARCHAR',
                    'constraint' => 191,
                    'null' => true,
                ],
                'username' => [
                    'type' => 'VARCHAR',
                    'constraint' => 191,
                    'null' => true,
                ],
                'request_body' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
                'response_body' => [
                    'type' => 'LONGTEXT',
                    'null' => true,
                ],
                'status_code' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => 200,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => false,
                ],
            ];

            $this->CI->dbforge->add_field($fields);
            $this->CI->dbforge->add_key('id', true);
            $created = $this->CI->dbforge->create_table($this->logTable, true);

            if (!$created) {
                log_message('error', 'Failed to auto-create tbl_api_logs table: ' . json_encode($this->CI->db->error()));
            }
        }

        // Ensure indexes exist (helpful even if table pre-existed)
        $this->ensureIndex('idx_api_logs_api_key', 'api_key');
        $this->ensureIndex('idx_api_logs_created_at', 'created_at');

        $this->tableEnsured = true;
    }

    /**
     * Add an index if it is missing from the logs table
     */
    private function ensureIndex($indexName, $columnList) {
        if (!$this->CI->db->table_exists($this->logTable)) {
            return;
        }

        $query = $this->CI->db->query('SHOW INDEX FROM ' . $this->logTable . ' WHERE Key_name = ?', [$indexName]);
        if ($query && $query->num_rows() === 0) {
            $this->CI->db->query('CREATE INDEX ' . $indexName . ' ON ' . $this->logTable . ' (' . $columnList . ')');
        }
    }
}

/**
 * Function to easily log API requests from anywhere in the application
 *
 * @param string $endpoint - The API endpoint called
 * @param string $method - HTTP method (GET, POST, etc.)
 * @param string $api_key - API key used for authentication
 * @param array $request_data - Request parameters/data
 * @param mixed $response_data - Response from the API call
 * @param int $status_code - HTTP status code of the response
 * @return bool - True on success, false on failure
 */
function log_api_request($endpoint, $method, $api_key, $request_data = [], $response_data = null, $status_code = 200) {
    return API_Logger::getInstance()->log_request($endpoint, $method, $api_key, $request_data, $response_data, $status_code);
}
?>
