<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Documentation_api extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function swagger()
    {
        $api_key = $this->input->get_request_header('X-API-Key');

        $swagger = [
            "openapi" => "3.0.0",
            "info" => [
                "version" => "1.0.0",
                "title" => "Ticket Support API"
            ],
            "servers" => [
                [
                    "url" => trim(base_url(), '/') . "/api/v1"
                ]
            ],
            "security" => [
                ["apiKey" => []]
            ],
            "components" => [
                "securitySchemes" => [
                    "apiKey" => [
                        "type" => "apiKey",
                        "in" => "header",
                        "name" => "X-API-Key"
                    ]
                ]
            ],
            "paths" => [
                "/create_ticket" => [
                    "post" => [
                        "summary" => "Create a new ticket with full parameters and file upload support",
                        "operationId" => "createTicket",
                        "tags" => ["tickets"],
                        "requestBody" => [
                            "required" => true,
                            "content" => [
                                "multipart/form-data" => [
                                    "schema" => [
                                        "type" => "object",
                                        "required" => ["subject", "message"],
                                        "properties" => [
                                            "subject" => [
                                                "type" => "string",
                                                "description" => "Ticket subject/title"
                                            ],
                                            "message" => [
                                                "type" => "string",
                                                "description" => "Ticket message/description"
                                            ],
                                            "priority" => [
                                                "type" => "integer",
                                                "description" => "Priority level ID"
                                            ],
                                            "department" => [
                                                "type" => "integer",
                                                "description" => "Department ID"
                                            ],
                                            "service" => [
                                                "type" => "integer",
                                                "description" => "Service ID"
                                            ],
                                            "assigned" => [
                                                "type" => "integer",
                                                "description" => "Staff member ID to assign ticket to"
                                            ],
                                            "sub_department" => [
                                                "type" => "integer",
                                                "description" => "Sub-department ID"
                                            ],
                                            "divisionid" => [
                                                "type" => "integer",
                                                "description" => "Division ID"
                                            ],
                                            "application_id" => [
                                                "type" => "integer",
                                                "description" => "Application ID"
                                            ],
                                            "admin" => [
                                                "type" => "integer",
                                                "description" => "Admin ID - owner of the ticket"
                                            ],
                                            "tags" => [
                                                "type" => "string",
                                                "description" => "Comma-separated tag names"
                                            ],
                                            "file1" => [
                                                "type" => "string",
                                                "format" => "binary",
                                                "description" => "First file attachment (images/documents)"
                                            ],
                                            "file2" => [
                                                "type" => "string",
                                                "format" => "binary",
                                                "description" => "Second file attachment (optional)"
                                            ],
                                            "file3" => [
                                                "type" => "string",
                                                "format" => "binary",
                                                "description" => "Third file attachment (optional)"
                                            ]
                                        ]
                                    ]
                                ],
                                "application/json" => [
                                    "schema" => [
                                        "type" => "object",
                                        "required" => ["subject", "message"],
                                        "properties" => [
                                            "subject" => [
                                                "type" => "string",
                                                "description" => "Ticket subject/title"
                                            ],
                                            "message" => [
                                                "type" => "string",
                                                "description" => "Ticket message/description"
                                            ],
                                            "priority" => [
                                                "type" => "integer",
                                                "description" => "Priority level ID"
                                            ],
                                            "department" => [
                                                "type" => "integer",
                                                "description" => "Department ID"
                                            ],
                                            "service" => [
                                                "type" => "integer",
                                                "description" => "Service ID"
                                            ],
                                            "assigned" => [
                                                "type" => "integer",
                                                "description" => "Staff member ID to assign ticket to"
                                            ],
                                            "sub_department" => [
                                                "type" => "integer",
                                                "description" => "Sub-department ID"
                                            ],
                                            "divisionid" => [
                                                "type" => "integer",
                                                "description" => "Division ID"
                                            ],
                                            "application_id" => [
                                                "type" => "integer",
                                                "description" => "Application ID"
                                            ],
                                            "admin" => [
                                                "type" => "integer",
                                                "description" => "Admin ID - owner of the ticket"
                                            ],
                                            "tags" => [
                                                "type" => "string",
                                                "description" => "Comma-separated tag names"
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        "responses" => [
                            "200" => [
                                "description" => "Ticket created successfully",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "ticket_number" => [
                                                    "type" => "string",
                                                    "description" => "Generated ticket number"
                                                ],
                                                "ticket_id" => [
                                                    "type" => "integer",
                                                    "description" => "Internal ticket ID"
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "400" => [
                                "description" => "Bad request - missing required fields",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "403" => [
                                "description" => "Forbidden - insufficient permissions",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                "/ticket/{ticket_number}" => [
                    "get" => [
                        "summary" => "Get ticket details",
                        "operationId" => "getTicket",
                        "tags" => ["tickets"],
                        "parameters" => [
                            [
                                "name" => "ticket_number",
                                "in" => "path",
                                "required" => true,
                                "schema" => ["type" => "string"]
                            ]
                        ],
                        "responses" => [
                            "200" => [
                                "description" => "Ticket details",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "ticket_number" => ["type" => "string"],
                                                "subject" => ["type" => "string"],
                                                "message" => ["type" => "string"],
                                                "status" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                "/get_divisions" => [
                    "get" => [
                        "summary" => "Get all divisions",
                        "operationId" => "getDivisions",
                        "tags" => ["general"],
                        "responses" => [
                            "200" => [
                                "description" => "List of all divisions",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "array",
                                            "items" => [
                                                "type" => "object",
                                                "properties" => [
                                                    "divisionid" => [
                                                        "type" => "integer",
                                                        "description" => "Division ID"
                                                    ],
                                                    "name" => [
                                                        "type" => "string",
                                                        "description" => "Division name"
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "403" => [
                                "description" => "Forbidden - insufficient permissions",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                "/get_departments" => [
                    "get" => [
                        "summary" => "Get departments for a specific division",
                        "operationId" => "getDepartments",
                        "tags" => ["general"],
                        "parameters" => [
                            [
                                "name" => "division_id",
                                "in" => "query",
                                "required" => true,
                                "schema" => [
                                    "type" => "integer"
                                ],
                                "description" => "Division ID to get departments for"
                            ]
                        ],
                        "responses" => [
                            "200" => [
                                "description" => "List of departments for the specified division",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "array",
                                            "items" => [
                                                "type" => "object",
                                                "properties" => [
                                                    "departmentid" => [
                                                        "type" => "integer",
                                                        "description" => "Department ID"
                                                    ],
                                                    "name" => [
                                                        "type" => "string",
                                                        "description" => "Department name"
                                                    ],
                                                   "has_sub_departments" => [
                                                        "type" => "boolean",
                                                        "description" => "Whether this department has sub-departments"
                                                    ],
                                                    "sub_departments" => [
                                                        "type" => "array",
                                                        "description" => "List of sub-departments (only included if has_sub_departments is true)",
                                                        "items" => [
                                                            "type" => "object",
                                                            "properties" => [
                                                                "id" => [
                                                                    "type" => "integer",
                                                                    "description" => "Sub-department ID"
                                                                ],
                                                                "name" => [
                                                                    "type" => "string",
                                                                    "description" => "Sub-department name"
                                                                ]
                                                            ]
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "400" => [
                                "description" => "Bad request - missing or invalid division_id parameter",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "403" => [
                                "description" => "Forbidden - insufficient permissions",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "404" => [
                                "description" => "Not found - division not found",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                "/get_applications" => [
                    "get" => [
                        "summary" => "Get applications for a specific department",
                        "operationId" => "getApplications",
                        "tags" => ["general"],
                        "parameters" => [
                            [
                                "name" => "department_id",
                                "in" => "query",
                                "required" => true,
                                "schema" => [
                                    "type" => "integer"
                                ],
                                "description" => "Department ID to get applications for"
                            ],
                            [
                                "name" => "sub_department_id",
                                "in" => "query",
                                "required" => false,
                                "schema" => [
                                    "type" => "integer"
                                ],
                                "description" => "Sub-department ID to filter applications (optional)"
                            ]
                        ],
                        "responses" => [
                            "200" => [
                                "description" => "List of applications for the specified department",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "array",
                                            "items" => [
                                                "type" => "object",
                                                "properties" => [
                                                    "id" => [
                                                        "type" => "integer",
                                                        "description" => "Application ID"
                                                    ],
                                                    "name" => [
                                                        "type" => "string",
                                                        "description" => "Application name"
                                                    ],
                                                    "service_id" => [
                                                        "type" => "integer",
                                                        "nullable" => true,
                                                        "description" => "Associated service ID"
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "400" => [
                                "description" => "Bad request - missing or invalid department_id parameter",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "403" => [
                                "description" => "Forbidden - insufficient permissions",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "404" => [
                                "description" => "Not found - department not found",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                "/get_services" => [
                    "get" => [
                        "summary" => "Get services for a specific application",
                        "operationId" => "getServices",
                        "tags" => ["general"],
                        "parameters" => [
                            [
                                "name" => "application_id",
                                "in" => "query",
                                "required" => true,
                                "schema" => [
                                    "type" => "integer"
                                ],
                                "description" => "Application ID to get services for"
                            ]
                        ],
                        "responses" => [
                            "200" => [
                                "description" => "List of services for the specified application",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "array",
                                            "items" => [
                                                "type" => "object",
                                                "properties" => [
                                                    "serviceid" => [
                                                        "type" => "integer",
                                                        "description" => "Service ID"
                                                    ],
                                                    "name" => [
                                                        "type" => "string",
                                                        "description" => "Service name"
                                                    ],
                                                    "responsible" => [
                                                        "type" => "integer",
                                                        "nullable" => true,
                                                        "description" => "Responsible staff member ID"
                                                    ],
                                                    "divisionid" => [
                                                        "type" => "integer",
                                                        "nullable" => true,
                                                        "description" => "Division ID"
                                                    ],
                                                    "departmentid" => [
                                                        "type" => "integer",
                                                        "nullable" => true,
                                                        "description" => "Department ID"
                                                    ],
                                                    "sub_department" => [
                                                        "type" => "integer",
                                                        "nullable" => true,
                                                        "description" => "Sub-department ID"
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "400" => [
                                "description" => "Bad request - missing or invalid application_id parameter",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "403" => [
                                "description" => "Forbidden - insufficient permissions",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "404" => [
                                "description" => "Not found - application not found",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                "/get_user_details" => [
                    "get" => [
                        "summary" => "Get user details by staff ID",
                        "operationId" => "getUserDetails",
                        "tags" => ["general"],
                        "parameters" => [
                            [
                                "name" => "userid",
                                "in" => "query",
                                "required" => true,
                                "schema" => [
                                    "type" => "integer"
                                ],
                                "description" => "User ID (staff member ID)"
                            ]
                        ],
                        "responses" => [
                            "200" => [
                                "description" => "User details retrieved successfully",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "success" => [
                                                    "type" => "boolean",
                                                    "example" => true
                                                ],
                                                "message" => [
                                                    "type" => "string",
                                                    "example" => "User details retrieved successfully"
                                                ],
                                                "data" => [
                                                    "type" => "object",
                                                    "properties" => [
                                                        "staffid" => [
                                                            "type" => "integer",
                                                            "description" => "Staff member ID"
                                                        ],
                                                        "firstname" => [
                                                            "type" => "string",
                                                            "description" => "First name"
                                                        ],
                                                        "lastname" => [
                                                            "type" => "string",
                                                            "description" => "Last name"
                                                        ],
                                                        "fullname" => [
                                                            "type" => "string",
                                                            "description" => "Full name"
                                                        ],
                                                        "email" => [
                                                            "type" => "string",
                                                            "description" => "Email address"
                                                        ],
                                                        "phonenumber" => [
                                                            "type" => "string",
                                                            "description" => "Phone number"
                                                        ],
                                                        "active" => [
                                                            "type" => "boolean",
                                                            "description" => "Account active status"
                                                        ],
                                                        "datecreated" => [
                                                            "type" => "string",
                                                            "description" => "Account creation date"
                                                        ],
                                                        "last_login" => [
                                                            "type" => "string",
                                                            "nullable" => true,
                                                            "description" => "Last login date"
                                                        ],
                                                        "role" => [
                                                            "type" => "object",
                                                            "properties" => [
                                                                "id" => [
                                                                    "type" => "integer",
                                                                    "nullable" => true
                                                                ],
                                                                "name" => [
                                                                    "type" => "string"
                                                                ]
                                                            ],
                                                            "description" => "User role information"
                                                        ],
                                                        "department" => [
                                                            "type" => "string",
                                                            "nullable" => true,
                                                            "description" => "Department name"
                                                        ],
                                                        "sub_department" => [
                                                            "type" => "string",
                                                            "nullable" => true,
                                                            "description" => "Sub-department name"
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "400" => [
                                "description" => "Bad request - missing or invalid userid parameter",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "403" => [
                                "description" => "Forbidden - insufficient permissions",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "404" => [
                                "description" => "User not found",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                "/get_user_details_by_emp_code" => [
                    "get" => [
                        "summary" => "Get user details by employee code",
                        "operationId" => "getUserDetailsByEmpCode",
                        "tags" => ["general"],
                        "parameters" => [
                            [
                                "name" => "emp_code",
                                "in" => "query",
                                "required" => true,
                                "schema" => [
                                    "type" => "string"
                                ],
                                "description" => "Employee code to search for"
                            ]
                        ],
                        "responses" => [
                            "200" => [
                                "description" => "User details retrieved successfully",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "success" => [
                                                    "type" => "boolean",
                                                    "example" => true
                                                ],
                                                "message" => [
                                                    "type" => "string",
                                                    "example" => "User details retrieved successfully"
                                                ],
                                                "data" => [
                                                    "type" => "object",
                                                    "properties" => [
                                                        "staffid" => [
                                                            "type" => "integer",
                                                            "description" => "Staff member ID"
                                                        ],
                                                        "emp_code" => [
                                                            "type" => "string",
                                                            "nullable" => true,
                                                            "description" => "Employee code associated with the staff member"
                                                        ],
                                                        "firstname" => [
                                                            "type" => "string",
                                                            "description" => "First name"
                                                        ],
                                                        "lastname" => [
                                                            "type" => "string",
                                                            "description" => "Last name"
                                                        ],
                                                        "fullname" => [
                                                            "type" => "string",
                                                            "description" => "Full name"
                                                        ],
                                                        "email" => [
                                                            "type" => "string",
                                                            "description" => "Email address"
                                                        ],
                                                        "phonenumber" => [
                                                            "type" => "string",
                                                            "description" => "Phone number"
                                                        ],
                                                        "active" => [
                                                            "type" => "boolean",
                                                            "description" => "Account active status"
                                                        ],
                                                        "datecreated" => [
                                                            "type" => "string",
                                                            "description" => "Account creation date"
                                                        ],
                                                        "last_login" => [
                                                            "type" => "string",
                                                            "nullable" => true,
                                                            "description" => "Last login date"
                                                        ],
                                                        "role" => [
                                                            "type" => "object",
                                                            "properties" => [
                                                                "id" => [
                                                                    "type" => "integer",
                                                                    "nullable" => true
                                                                ],
                                                                "name" => [
                                                                    "type" => "string"
                                                                ]
                                                            ],
                                                            "description" => "User role information"
                                                        ],
                                                        "department" => [
                                                            "type" => "string",
                                                            "nullable" => true,
                                                            "description" => "Department name"
                                                        ],
                                                        "sub_department" => [
                                                            "type" => "string",
                                                            "nullable" => true,
                                                            "description" => "Sub-department name"
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "400" => [
                                "description" => "Bad request - missing or invalid emp_code parameter",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "403" => [
                                "description" => "Forbidden - insufficient permissions",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "404" => [
                                "description" => "User not found",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                "/get_user_details_by_emp_phonenum" => [
                    "get" => [
                        "summary" => "Get user details by employee phone number",
                        "operationId" => "getUserDetailsByEmpPhone",
                        "tags" => ["general"],
                        "parameters" => [
                            [
                                "name" => "emp_phonenum",
                                "in" => "query",
                                "required" => true,
                                "schema" => [
                                    "type" => "string"
                                ],
                                "description" => "Employee phone number to search for"
                            ]
                        ],
                        "responses" => [
                            "200" => [
                                "description" => "User details retrieved successfully",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "success" => [
                                                    "type" => "boolean",
                                                    "example" => true
                                                ],
                                                "message" => [
                                                    "type" => "string",
                                                    "example" => "User details retrieved successfully"
                                                ],
                                                "data" => [
                                                    "type" => "object",
                                                    "properties" => [
                                                        "staffid" => [
                                                            "type" => "integer",
                                                            "description" => "Staff member ID"
                                                        ],
                                                        "emp_phone" => [
                                                            "type" => "string",
                                                            "description" => "Phone number used for lookup"
                                                        ],
                                                        "emp_code" => [
                                                            "type" => "string",
                                                            "nullable" => true,
                                                            "description" => "Employee code associated with the staff member"
                                                        ],
                                                        "firstname" => [
                                                            "type" => "string",
                                                            "description" => "First name"
                                                        ],
                                                        "lastname" => [
                                                            "type" => "string",
                                                            "description" => "Last name"
                                                        ],
                                                        "fullname" => [
                                                            "type" => "string",
                                                            "description" => "Full name"
                                                        ],
                                                        "email" => [
                                                            "type" => "string",
                                                            "description" => "Email address"
                                                        ],
                                                        "phonenumber" => [
                                                            "type" => "string",
                                                            "description" => "Primary phone number on record"
                                                        ],
                                                        "active" => [
                                                            "type" => "boolean",
                                                            "description" => "Account active status"
                                                        ],
                                                        "datecreated" => [
                                                            "type" => "string",
                                                            "description" => "Account creation date"
                                                        ],
                                                        "last_login" => [
                                                            "type" => "string",
                                                            "nullable" => true,
                                                            "description" => "Last login date"
                                                        ],
                                                        "role" => [
                                                            "type" => "object",
                                                            "properties" => [
                                                                "id" => [
                                                                    "type" => "integer",
                                                                    "nullable" => true
                                                                ],
                                                                "name" => [
                                                                    "type" => "string"
                                                                ]
                                                            ],
                                                            "description" => "User role information"
                                                        ],
                                                        "department" => [
                                                            "type" => "string",
                                                            "nullable" => true,
                                                            "description" => "Department name"
                                                        ],
                                                        "sub_department" => [
                                                            "type" => "string",
                                                            "nullable" => true,
                                                            "description" => "Sub-department name"
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "400" => [
                                "description" => "Bad request - missing or invalid emp_phonenum parameter",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "403" => [
                                "description" => "Forbidden - insufficient permissions",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            "404" => [
                                "description" => "User not found",
                                "content" => [
                                    "application/json" => [
                                        "schema" => [
                                            "type" => "object",
                                            "properties" => [
                                                "error" => ["type" => "string"]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($swagger));

        // Log successful request
        log_api_request('/api/v1/swagger', 'GET', $api_key, [], $swagger, 200);
    }
}
