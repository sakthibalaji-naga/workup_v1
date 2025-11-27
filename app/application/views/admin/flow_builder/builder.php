<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<script>
if (document && document.body && !document.body.classList.contains('flow-builder-fullscreen')) {
    document.body.classList.add('flow-builder-fullscreen');
}
</script>
<div id="wrapper">
    <div class="content flow-builder-content">
        <div class="flow-builder-shell">
            <header class="flow-builder-header">
                <div class="header-left">
                    <button type="button" class="builder-nav-button" id="flow-builder-back-icon" title="Back to flows" data-flow-action="back">
                        <i class="fa fa-arrow-left"></i>
                    </button>
                    <div class="builder-heading-group">
                        <span class="builder-badge"><?php echo _l('build'); ?></span>
                        <h2 class="builder-heading mb-0"><?php echo _l('flow_builder'); ?></h2>
                        <p class="builder-subtitle mb-0"><?php echo _l('flow_builder_description'); ?></p>
                    </div>
                </div>
                <div class="header-actions">
                    <button type="button" class="btn btn-outline-secondary btn-pill stage-fit-button" data-flow-action="fit" title="Fit to screen">
                        <i class="fa fa-expand"></i>
                    </button>
                    <button type="button" class="btn btn-primary btn-pill" data-flow-action="save">
                        <i class="fa fa-save"></i> <?php echo _l('save_flow'); ?>
                    </button>
                    <button type="button" class="btn btn-success btn-pill" data-flow-action="test">
                        <i class="fa fa-play"></i> <?php echo _l('test_flow'); ?>
                    </button>
                    <button type="button" class="btn btn-info btn-pill" data-flow-action="export">
                        <i class="fa fa-download"></i> <?php echo _l('export_flow'); ?>
                    </button>
                </div>
            </header>
            <div class="flow-builder-details-section">
                <div id="flow-details-panel" class="flow-details-panel"></div>
            </div>
            <div class="flow-builder-body">
                <aside class="flow-builder-sidebar">
                    <div class="sidebar-header">
                        <h5 class="sidebar-title"><?php echo _l('available_flow_components'); ?></h5>
                        <p class="sidebar-subtitle text-muted"><?php echo _l('flow_builder_get_started_helper'); ?></p>
                    </div>
                    <div class="sidebar-search">
                        <i class="fa fa-search"></i>
                        <input type="text" id="component-search" placeholder="Search components..." autocomplete="off">
                    </div>
                    <div class="components-scroll">
                        <div class="components-container">
                            <!-- Triggers -->
                            <div class="component-category">
                                <h6 class="component-category-title">
                                    <i class="fa fa-play-circle text-primary"></i>
                                    <?php echo _l('api_response'); ?>
                                </h6>
                                <div class="component-items" id="trigger-components">
                                    <div class="component-item" draggable="true" data-type="api_trigger">
                                        <div class="component-icon">
                                            <i class="fa fa-exchange"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('api_response'); ?></div>
                                            <small class="component-description">Triggered by API response</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Conditions -->
                            <div class="component-category">
                                <h6 class="component-category-title">
                                    <i class="fa fa-code-fork text-success"></i>
                                    <?php echo _l('conditions'); ?>
                                </h6>
                                <div class="component-items" id="condition-components">
                                    <div class="component-item" draggable="true" data-type="condition">
                                        <div class="component-icon">
                                            <i class="fa fa-code-fork"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('set_condition'); ?></div>
                                            <small class="component-description">Set condition based on response</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Actions -->
                            <div class="component-category">
                                <h6 class="component-category-title">
                                    <i class="fa fa-cogs text-info"></i>
                                    <?php echo _l('actions'); ?>
                                </h6>
                                <div class="component-items" id="action-components">
                                    <!-- Staff Actions -->
                                    <div class="component-item" draggable="true" data-type="staff_create">
                                        <div class="component-icon" style="background-color: #17a2b8;">
                                            <i class="fa fa-user-plus"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('staff_create'); ?></div>
                                            <small class="component-description">Create new staff member</small>
                                        </div>
                                    </div>
                                    <div class="component-item" draggable="true" data-type="staff_update">
                                        <div class="component-icon" style="background-color: #17a2b8;">
                                            <i class="fa fa-user-edit"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('staff_update'); ?></div>
                                            <small class="component-description">Update existing staff member</small>
                                        </div>
                                    </div>
                                    <!-- Ticket Actions -->
                                    <div class="component-item" draggable="true" data-type="ticket_create">
                                        <div class="component-icon" style="background-color: #ffc107;">
                                            <i class="fa fa-plus-circle"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('ticket_create'); ?></div>
                                            <small class="component-description">Create new ticket</small>
                                        </div>
                                    </div>
                                    <div class="component-item" draggable="true" data-type="ticket_update">
                                        <div class="component-icon" style="background-color: #ffc107;">
                                            <i class="fa fa-edit"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('ticket_update'); ?></div>
                                            <small class="component-description">Update existing ticket</small>
                                        </div>
                                    </div>
                                    <!-- Division Actions -->
                                    <div class="component-item" draggable="true" data-type="division_create">
                                        <div class="component-icon" style="background-color: #6610f2;">
                                            <i class="fa fa-sitemap"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('division_create'); ?></div>
                                            <small class="component-description">Create new division</small>
                                        </div>
                                    </div>
                                    <div class="component-item" draggable="true" data-type="division_update">
                                        <div class="component-icon" style="background-color: #6610f2;">
                                            <i class="fa fa-sitemap"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('division_update'); ?></div>
                                            <small class="component-description">Update existing division</small>
                                        </div>
                                    </div>
                                    <!-- Department Actions -->
                                    <div class="component-item" draggable="true" data-type="department_create">
                                        <div class="component-icon" style="background-color: #e83e8c;">
                                            <i class="fa fa-building"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('department_create'); ?></div>
                                            <small class="component-description">Create new department</small>
                                        </div>
                                    </div>
                                    <div class="component-item" draggable="true" data-type="department_update">
                                        <div class="component-icon" style="background-color: #e83e8c;">
                                            <i class="fa fa-building"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('department_update'); ?></div>
                                            <small class="component-description">Update existing department</small>
                                        </div>
                                    </div>
                                    <!-- Application Actions -->
                                    <div class="component-item" draggable="true" data-type="application_create">
                                        <div class="component-icon" style="background-color: #20c997;">
                                            <i class="fa fa-layer-group"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('application_create'); ?></div>
                                            <small class="component-description">Create new application</small>
                                        </div>
                                    </div>
                                    <div class="component-item" draggable="true" data-type="application_update">
                                        <div class="component-icon" style="background-color: #20c997;">
                                            <i class="fa fa-layer-group"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('application_update'); ?></div>
                                            <small class="component-description">Update existing application</small>
                                        </div>
                                    </div>
                                    <!-- Documenting -->
                                    <div class="component-item" draggable="true" data-type="documentation_create">
                                        <div class="component-icon" style="background-color: #fd7e14;">
                                            <i class="fa fa-file-alt"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('documentation_create'); ?></div>
                                            <small class="component-description">Create new documentation</small>
                                        </div>
                                    </div>
                                    <div class="component-item" draggable="true" data-type="documentation_update">
                                        <div class="component-icon" style="background-color: #fd7e14;">
                                            <i class="fa fa-file-alt"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('documentation_update'); ?></div>
                                            <small class="component-description">Update documentation</small>
                                        </div>
                                    </div>
                                    <!-- Services -->
                                    <div class="component-item" draggable="true" data-type="service_create">
                                        <div class="component-icon" style="background-color: #6f42c1;">
                                            <i class="fa fa-tools"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('service_create'); ?></div>
                                            <small class="component-description">Create service</small>
                                        </div>
                                    </div>
                                    <div class="component-item" draggable="true" data-type="service_update">
                                        <div class="component-icon" style="background-color: #6f42c1;">
                                            <i class="fa fa-tools"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('service_update'); ?></div>
                                            <small class="component-description">Update service</small>
                                        </div>
                                    </div>
                                    <!-- Notifications -->
                                    <div class="component-item" draggable="true" data-type="notification_create">
                                        <div class="component-icon" style="background-color: #6610f2;">
                                            <i class="fa fa-bell"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('notification_create'); ?></div>
                                            <small class="component-description">Create notification</small>
                                        </div>
                                    </div>
                                    <div class="component-item" draggable="true" data-type="notification_update">
                                        <div class="component-icon" style="background-color: #6610f2;">
                                            <i class="fa fa-bell"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('notification_update'); ?></div>
                                            <small class="component-description">Update notification</small>
                                        </div>
                                    </div>
                                    <!-- SMS & Messaging -->
                                    <div class="component-item" draggable="true" data-type="sms_send">
                                        <div class="component-icon" style="background-color: #17a2b8;">
                                            <i class="fa fa-sms"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('sms_send'); ?></div>
                                            <small class="component-description">Send SMS message</small>
                                        </div>
                                    </div>
                                    <div class="component-item" draggable="true" data-type="whatsapp_send">
                                        <div class="component-icon" style="background-color: #25d366;">
                                            <i class="fa fa-whatsapp"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('whatsapp_send'); ?></div>
                                            <small class="component-description">Send WhatsApp message</small>
                                        </div>
                                    </div>
                                    <div class="component-item" draggable="true" data-type="email_send">
                                        <div class="component-icon" style="background-color: #dc3545;">
                                            <i class="fa fa-envelope"></i>
                                        </div>
                                        <div class="component-info">
                                            <div class="component-name"><?php echo _l('email_send'); ?></div>
                                            <small class="component-description">Send email</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
                <section class="flow-builder-stage">
                    <div class="stage-header">
                        <div>
                            <span class="stage-label">Canvas</span>
                            <h3 class="stage-title mb-1"><?php echo _l('build'); ?></h3>
                            <p class="stage-subtitle text-muted"><?php echo _l('flow_builder_get_started_helper'); ?></p>
                        </div>
                        <div class="stage-hint">
                            <i class="fa fa-mouse-pointer"></i>
                            <span>Drag components onto the workspace and connect the dots.</span>
                        </div>
                    </div>
                    <div class="stage-canvas">
                        <div id="flow-canvas" class="flow-canvas" data-has-nodes="false">
                            <div class="canvas-placeholder" id="canvas-placeholder">
                                <div class="placeholder-content">
                                    <span class="placeholder-icon">
                                        <i class="fa fa-object-group"></i>
                                    </span>
                                    <h5 class="placeholder-title"><?php echo _l('flow_builder_get_started'); ?></h5>
                                    <p class="placeholder-text"><?php echo _l('flow_builder_get_started_helper'); ?></p>
                                </div>
                            </div>
                            <div class="canvas-container">
                                <!-- This will be populated by React Flow -->
                            </div>
                        </div>

                    </div>
                </section>
                <aside class="flow-builder-inspector">
                    <div id="flow-details-panel" class="inspector-panel"></div>
                    <div id="node-inspector-panel" class="inspector-panel inspector-empty">
                        <div class="inspector-empty-state">
                            <span class="inspector-pill">
                                <i class="fa fa-magic"></i>
                            </span>
                            <h6>Select a step</h6>
                            <p>Click a block on the canvas to see its summary, variables, and quick actions here.</p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</div>

<!-- Response Variables Panel Template -->
<template id="responseVariablesPanelTemplate">
    <div class="response-variable-panel shadow-sm">
        <div class="response-variable-panel-header">
            <h6 class="mb-2 font-weight-bold">Response Variable Mappings</h6>
            <p class="text-muted small mb-3">Add descriptive names and link them to response fields. Raw payload is auto-stored as <code>{{variable_name}}_raw</code>.</p>
            <button type="button" class="btn btn-outline-success btn-sm btn-with-icon" onclick="addResponseVariableRow()">
                <i class="fa fa-plus-circle"></i><span>Add Variable</span>
            </button>
        </div>
        <div id="responseVariablesList" class="response-variables-list"></div>
        <div class="text-right mt-3">
            <button type="button" class="btn btn-primary btn-with-icon" onclick="saveResponseVariables()">
                <i class="fa fa-save"></i><span>Save Variables</span>
            </button>
        </div>
    </div>
</template>
<div id="node-context-menu" class="node-context-menu" role="menu">
    <button type="button" class="context-menu-item" data-action="configure">
        <i class="fa fa-cog" aria-hidden="true"></i> <?php echo _l('flow_node_action_configure'); ?>
    </button>
    <button type="button" class="context-menu-item" data-action="duplicate">
        <i class="fa fa-clone" aria-hidden="true"></i> <?php echo _l('flow_node_action_duplicate'); ?>
    </button>
    <div class="context-menu-divider" role="separator"></div>
    <button type="button" class="context-menu-item" data-action="delete">
        <i class="fa fa-trash" aria-hidden="true"></i> <?php echo _l('flow_node_action_delete'); ?>
    </button>
</div>
<!-- Node Configuration Modal -->
<div class="modal fade" id="nodeConfigModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="nodeConfigTitle">Node Configuration</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="nodeConfigBody">
                <!-- Configuration content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="event.stopPropagation();">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="event.stopPropagation(); saveNodeConfiguration(); return false;">Save Configuration</button>
            </div>
        </div>
    </div>
</div>
<!-- API Response Viewer Modal -->
<div class="modal fade" id="apiResponseModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="apiResponseTitle">API Response Details</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="apiResponseBody">
                <div class="api-response-container">
                    <ul class="nav nav-tabs" id="responseTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="structure-tab" data-toggle="tab" href="#response-structure" role="tab">Response Structure</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="raw-tab" data-toggle="tab" href="#response-raw" role="tab">Raw Response</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="variables-tab" data-toggle="tab" href="#response-variables" role="tab">Variable Mapping</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="responseTabContent">
                        <div class="tab-pane fade show active" id="response-structure" role="tabpanel">
                            <div class="structure-viewer" id="responseStructureContainer">
                                <!-- Response structure will be displayed here -->
                            </div>
                        </div>
                        <div class="tab-pane fade" id="response-raw" role="tabpanel">
                            <div class="raw-response-viewer">
                                <pre id="rawResponseText" class="raw-response-text"></pre>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="response-variables" role="tabpanel">
                            <div class="variables-mapping-container" id="variablesMappingContainer">
                                <!-- Variable mapping options will be displayed here -->
                                <div class="alert alert-info">
                                    <h6><i class="fa fa-info-circle"></i> Variable Creation Guide</h6>
                                    <p>This feature allows you to extract specific fields from the API response and create reusable variables for use throughout your flow.</p>
                                    <p><strong>How to create variables:</strong></p>
                                    <ol>
                                        <li>Click on any field in the response structure above</li>
                                        <li>Give it a meaningful variable name (e.g., "user_id", "customer_name")</li>
                                        <li>The variable will be available in all downstream nodes as <code>{{variable_name}}</code></li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info" onclick="createVariableFromResponse()">
                    <i class="fa fa-plus"></i> Create Variable
                </button>
                <button type="button" class="btn btn-primary" onclick="applyResponseMapping()">
                    <i class="fa fa-save"></i> Apply Mapping
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Variable Picker Modal -->
<div class="modal fade" id="variablePickerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="variablePickerTitle">Variable Picker</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="variablePickerBody">
                <div class="form-group">
                    <label>Available Variables:</label>
                    <div id="variable-list" class="variable-list">
                        <!-- Variables will be populated here -->
                    </div>
                </div>
                <div class="form-group">
                    <label>Preview:</label>
                    <div id="variable-preview" class="variable-preview">
                        Select a variable to see its structure
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="insertSelectedVariable()">Insert Variable</button>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<!-- Disable lightbox initialization -->
<script>
// Disable lightbox plugin to prevent HTML injection
if (window.lightbox && typeof window.lightbox === 'function' && window.lightbox.prototype && window.lightbox.prototype.build) {
    // Override the build method to prevent HTML injection
    const originalBuild = window.lightbox.prototype.build;
    if (typeof originalBuild === 'function') {
        window.lightbox.prototype.build = function() {
            // Do nothing - prevent lightbox HTML from being added to the page
        };
    }
}

// Or prevent lightbox enable completely
$(document).off('click', 'a[rel^=lightbox], area[rel^=lightbox], a[data-lightbox], area[data-lightbox]');

// Remove any existing lightbox elements that might have been added
$(document).ready(function() {
    $('#lightbox, .lightbox, #lightboxOverlay, .lightboxOverlay').remove();
});
</script>
<!-- React Flow CSS -->
<link href="https://unpkg.com/reactflow@11.8.3/dist/style.css" rel="stylesheet">

<!-- React Flow JS -->
<script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
<script>
    window.jsxRuntime = window.jsxRuntime || (function() {
        const assign = Object.assign || function(target) {
            if (target == null) {
                throw new TypeError('Cannot convert undefined or null to object');
            }
            for (let i = 1; i < arguments.length; i++) {
                const source = arguments[i];
                if (source != null) {
                    for (const key in source) {
                        if (Object.prototype.hasOwnProperty.call(source, key)) {
                            target[key] = source[key];
                        }
                    }
                }
            }
            return target;
        };
        const createElement = function(type, props, key) {
            const finalProps = props ? assign({}, props) : {};
            const children = finalProps.children;
            if (typeof children !== 'undefined') {
                delete finalProps.children;
            }
            if (typeof key !== 'undefined') {
                finalProps.key = key;
            }
            if (typeof children === 'undefined') {
                return React.createElement(type, finalProps);
            }
            return Array.isArray(children)
                ? React.createElement.apply(null, [type, finalProps].concat(children))
                : React.createElement(type, finalProps, children);
        };
        return {
            Fragment: React.Fragment,
            jsx: createElement,
            jsxs: createElement,
            jsxDEV: createElement
        };
    })();
</script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
<script src="https://unpkg.com/reactflow@11.8.3/dist/umd/index.js"></script>
<style>
:root {
    --flow-panel-bg: #ffffff;
    --flow-border: #d8dee6;
    --flow-accent: #0d6efd;
    --flow-text-muted: #6b7280;
    --flow-shadow: 0 18px 36px rgba(15, 23, 42, 0.18);
}

.flow-builder-content {
    background: linear-gradient(180deg, #f3f5ff 0%, #eef2ff 100%);
    padding: 32px 24px 48px;
}
.flow-builder-shell {
    display: flex;
    flex-direction: column;
    gap: 28px;
}
body.flow-builder-fullscreen #setup-menu,
body.flow-builder-fullscreen #setup-menu-wrapper,
body.flow-builder-fullscreen #side-menu,
body.flow-builder-fullscreen #mobile-menu,
body.flow-builder-fullscreen .mobile-menu-toggle {
    display: none !important;
}
body.flow-builder-fullscreen #wrapper,
body.flow-builder-fullscreen #content {
    margin-left: 0 !important;
}
body.flow-builder-fullscreen #menu,
body.flow-builder-fullscreen .mobile-menu,
body.flow-builder-fullscreen .mobile-menu-wrapper,
body.flow-builder-fullscreen .menu-overlay,
body.flow-builder-fullscreen .mobile-menu-overlay {
    display: none !important;
}
.flow-builder-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    padding: 24px 28px;
    border-radius: 24px;
    background: radial-gradient(160% 120% at 0% 0%, rgba(99, 102, 241, 0.12) 0%, rgba(99, 102, 241, 0.06) 42%, rgba(148, 163, 184, 0.1) 100%), #ffffff;
    box-shadow: 0 30px 60px rgba(15, 23, 42, 0.08);
    border: 1px solid rgba(148, 163, 184, 0.16);
}
.header-left {
    display: flex;
    align-items: center;
    gap: 18px;
}
.builder-nav-button {
    width: 44px;
    height: 44px;
    border-radius: 999px;
    border: 1px solid rgba(148, 163, 184, 0.32);
    background: #ffffff;
    color: #1f2937;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 16px 28px rgba(15, 23, 42, 0.12);
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}
.builder-nav-button:hover {
    transform: translateY(-2px);
    border-color: rgba(99, 102, 241, 0.45);
    box-shadow: 0 18px 32px rgba(99, 102, 241, 0.18);
    color: #4c51bf;
}
.builder-heading-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.builder-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(79, 70, 229, 0.12);
    color: #4338ca;
    font-weight: 600;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    padding: 6px 12px;
    border-radius: 999px;
}
.builder-heading {
    font-size: 1.65rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}
.builder-subtitle {
    margin: 0;
    font-size: 0.95rem;
    color: #475569;
}
.header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}
.btn-pill {
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 10px 18px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 16px 30px rgba(15, 23, 42, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.btn-pill .fa {
    font-size: 0.85rem;
}
.btn-pill:hover {
    transform: translateY(-1px);
    box-shadow: 0 20px 36px rgba(15, 23, 42, 0.15);
}
.stage-fit-button {
    padding: 10px 12px;
    font-size: 0;
    line-height: 0;
    min-width: 44px;
}
.stage-fit-button .fa {
    font-size: 0.9rem;
}
.flow-builder-body {
    display: grid;
    grid-template-columns: 320px minmax(0, 1fr) 320px;
    gap: 24px;
    align-items: flex-start;
}
.flow-builder-sidebar,
.flow-builder-stage,
.flow-builder-inspector {
    background: #ffffff;
    border-radius: 24px;
    border: 1px solid rgba(148, 163, 184, 0.16);
    box-shadow: 0 22px 45px rgba(15, 23, 42, 0.09);
    position: relative;
}
.flow-builder-sidebar {
    padding: 24px 24px 20px;
    display: flex;
    flex-direction: column;
    gap: 20px;
    min-height: 0;
    height: 100%;
    max-height: 800px;
}
.sidebar-header {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.sidebar-title {
    margin: 0;
    font-weight: 700;
    font-size: 1rem;
    color: #111827;
}
.sidebar-subtitle {
    margin: 0;
    font-size: 0.85rem;
    color: #64748b;
}
.sidebar-search {
    display: flex;
    align-items: center;
    gap: 10px;
    border: 1px solid rgba(148, 163, 184, 0.32);
    border-radius: 16px;
    padding: 10px 14px;
    background: #f8fafc;
    box-shadow: inset 0 1px 3px rgba(148, 163, 184, 0.1);
}
.sidebar-search i {
    color: #64748b;
    font-size: 0.9rem;
}
.components-scroll {
    flex: 1;
    overflow-y: auto;
    padding-right: 6px;
    margin-right: -6px;
    min-height: 0;
    max-height: 600px; /* Limit max height to ensure scrolling */
}
.components-scroll::-webkit-scrollbar {
    width: 6px;
}
.components-scroll::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.45);
    border-radius: 999px;
}
.flow-builder-stage {
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    min-height: 0;
}
.stage-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 16px;
}
.stage-label {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #6366f1;
}
.stage-title {
    margin: 4px 0 0 0;
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
}
.stage-subtitle {
    margin: 6px 0 0 0;
    color: #6b7280;
    font-size: 0.9rem;
}
.stage-hint {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 999px;
    background: rgba(99, 102, 241, 0.08);
    color: #4338ca;
    font-weight: 600;
    font-size: 0.85rem;
    box-shadow: 0 12px 26px rgba(99, 102, 241, 0.12);
}
.stage-hint .fa {
    font-size: 0.95rem;
}
.stage-canvas {
    position: relative;
    flex: 1;
    min-height: 520px;
}

.flow-builder-inspector {
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    min-height: 0;
}
.inspector-panel {
    border-radius: 20px;
    border: 1px solid rgba(148, 163, 184, 0.26);
    padding: 18px;
    background: #ffffff;
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.12);
}
.inspector-empty {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 220px;
}
.inspector-empty-state {
    text-align: center;
    color: #475569;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
.inspector-pill {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.22), rgba(14, 165, 233, 0.2));
    color: #4338ca;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    box-shadow: 0 12px 26px rgba(79, 70, 229, 0.18);
}
.inspector-empty-state h6 {
    margin: 0;
    font-weight: 700;
    color: #111827;
}
.inspector-empty-state p {
    margin: 0;
    font-size: 0.85rem;
    line-height: 1.5;
}
.inspector-node {
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.inspector-node-header {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.inspector-node-chip {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    border: 1px solid rgba(99, 102, 241, 0.25);
    background: rgba(99, 102, 241, 0.12);
    color: #4338ca;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    box-shadow: 0 12px 24px rgba(79, 70, 229, 0.18);
}
.inspector-node-titles {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
    flex: 1;
}
.inspector-node-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
}
.inspector-node-type {
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #6366f1;
}
.inspector-node-status {
    font-size: 0.72rem;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid rgba(99, 102, 241, 0.25);
    background: rgba(99, 102, 241, 0.12);
    color: #4338ca;
}
.inspector-node-status.needs-setup {
    color: #b91c1c;
    background: rgba(239, 68, 68, 0.12);
    border-color: rgba(239, 68, 68, 0.25);
}
.inspector-node-body {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.inspector-node-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.inspector-node-list li {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    font-size: 0.85rem;
    color: #475569;
    background: rgba(148, 163, 184, 0.16);
    border-radius: 12px;
    padding: 10px 14px;
}
.inspector-node-list li span:first-child {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #64748b;
}
.inspector-node-list li span:last-child {
    font-weight: 600;
    color: #0f172a;
    word-break: break-word;
}
.inspector-node-hint {
    margin: 0;
    font-size: 0.78rem;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.inspector-node-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.inspector-action {
    border: none;
    border-radius: 12px;
    background: rgba(99, 102, 241, 0.12);
    color: #4338ca;
    padding: 8px 14px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}
.inspector-action:hover {
    transform: translateY(-1px);
    background: rgba(99, 102, 241, 0.2);
}
.inspector-action.danger {
    background: rgba(239, 68, 68, 0.12);
    color: #b91c1c;
}
.inspector-action.danger:hover {
    background: rgba(239, 68, 68, 0.18);
}
.flow-details-card {
    display: flex;
    flex-direction: column;
    gap: 20px;
    background: linear-gradient(180deg, rgba(241, 245, 255, 0.8) 0%, rgba(255, 255, 255, 0.95) 100%);
    border-radius: 20px;
    border: 1px solid rgba(148, 163, 184, 0.3);
    padding: 22px;
    box-shadow: 0 18px 36px rgba(15, 23, 42, 0.14);
}
.flow-details-header {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.flow-details-icon {
    width: 46px;
    height: 46px;
    border-radius: 16px;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.25), rgba(14, 165, 233, 0.22));
    color: #4338ca;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 14px 26px rgba(79, 70, 229, 0.18);
    font-size: 1rem;
}
.flow-details-heading {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 700;
    color: #111827;
}
.flow-details-titles {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.flow-details-subtitle {
    margin: 6px 0 0 0;
    font-size: 0.85rem;
    color: #64748b;
}
.flow-details-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 16px;
}
.flow-details-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.flow-details-label {
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #475569;
}
.flow-details-required {
    margin-left: 4px;
    color: #ef4444;
    font-size: 0.8rem;
}
.flow-details-input,
.flow-details-textarea {
    border-radius: 12px;
    border: 1px solid rgba(148, 163, 184, 0.35);
    background: rgba(255, 255, 255, 0.9);
    padding: 11px 14px;
    font-size: 0.95rem;
    color: #1f2937;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
    width: 100%;
    box-shadow: inset 0 1px 2px rgba(148, 163, 184, 0.08);
}
.flow-details-input:focus,
.flow-details-textarea:focus {
    outline: none;
    border-color: rgba(99, 102, 241, 0.55);
    box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.16);
}
.flow-details-textarea {
    min-height: 96px;
    resize: vertical;
}
.flow-details-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-top: 12px;
    border-top: 1px dashed rgba(148, 163, 184, 0.4);
}
.flow-details-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #0f766e;
    background: rgba(16, 185, 129, 0.12);
    padding: 8px 12px;
    border-radius: 999px;
}
.flow-details-status .fa {
    font-size: 0.75rem;
}
.flow-details-meta {
    font-size: 0.8rem;
    color: #94a3b8;
}
@media (min-width: 768px) {
    .flow-details-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

/* Hide Bootstrap Lightbox */
#lightbox, .lightbox, #lightboxOverlay, .lightboxOverlay {
    display: none !important;
}
.flow-canvas {
    width: 100%;
    min-height: 560px;
    height: clamp(560px, calc(100vh - 320px), 880px);
    border: 1px solid rgba(148, 163, 184, 0.32);
    border-radius: 18px;
    position: relative;
    background: radial-gradient(circle at 12% 0%, rgba(99, 102, 241, 0.16) 0%, rgba(99, 102, 241, 0.05) 45%, rgba(255, 255, 255, 0) 70%), #f6f7fb;
    overflow: hidden;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.flow-canvas::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: linear-gradient(rgba(148, 163, 184, 0.16) 1px, transparent 1px), linear-gradient(90deg, rgba(148, 163, 184, 0.16) 1px, transparent 1px);
    background-size: 36px 36px;
    pointer-events: none;
    opacity: 0.35;
    z-index: 0;
}
.flow-canvas.is-drop-target {
    border-color: rgba(13, 110, 253, 0.65);
    box-shadow: 0 14px 40px rgba(13, 110, 253, 0.20);
}
.canvas-container {
    width: 100%;
    height: 100%;
    position: relative;
    z-index: 2;
}
.canvas-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 48px 24px;
    z-index: 1;
    transition: opacity 0.25s ease, transform 0.25s ease;
    pointer-events: none;
}
.flow-canvas[data-has-nodes="true"] .canvas-placeholder {
    opacity: 0;
    transform: translateY(18px);
}
.placeholder-content {
    max-width: 320px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    align-items: center;
    color: #1f2937;
}
.placeholder-icon {
    width: 64px;
    height: 64px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(13, 110, 253, 0.12);
    color: var(--flow-accent);
    font-size: 24px;
}
.placeholder-title {
    font-weight: 600;
    margin: 0;
}
.placeholder-text {
    margin: 0;
    font-size: 14px;
    color: var(--flow-text-muted);
}
.components-container {
    display: flex;
    flex-direction: column;
    gap: 18px;
    padding-right: 2px;
}
.component-category {
    border: 1px solid rgba(148, 163, 184, 0.24);
    border-radius: 14px;
    padding: 18px;
    background: var(--flow-panel-bg);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
}
.component-category-title {
    margin: 0 0 16px 0;
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 10px;
}
.component-items {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
}
.component-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px;
    background: #f8fafc;
    border: 1px solid rgba(148, 163, 184, 0.22);
    border-radius: 12px;
    cursor: grab;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    position: relative;
    min-height: 72px;
}
.component-item:hover {
    transform: translateY(-2px);
    border-color: rgba(13, 110, 253, 0.4);
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
    background: #ffffff;
}
.component-item:active {
    cursor: grabbing;
    transform: scale(0.98);
}
.component-item:focus {
    outline: none;
    border-color: rgba(13, 110, 253, 0.6);
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
}
.component-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #fff;
    flex-shrink: 0;
    transition: transform 0.2s ease;
}
.component-item:hover .component-icon {
    transform: scale(1.08) rotate(-2deg);
}
.component-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
    text-align: left;
}
.component-name {
    font-weight: 600;
    color: #111827;
    margin: 0;
}
.component-description {
    color: var(--flow-text-muted);
    font-size: 12px;
    margin: 0;
}
#component-search {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    padding: 0;
    font-size: 0.95rem;
    color: #1f2937;
}
#component-search::placeholder {
    color: #94a3b8;
}
#component-search:focus {
    border: none;
    box-shadow: none;
}
.react-flow__node {
    padding: 0;
    border: none;
    background: transparent;
    box-shadow: none;
    min-width: 200px;
}
.node-card {
    position: relative;
    display: flex;
    flex-direction: column;
    gap: 14px;
    padding: 14px 16px;
    min-width: 180px;
    border-radius: 16px;
    border: 1px solid var(--node-border, rgba(148, 163, 184, 0.25));
    background: #ffffff;
    box-shadow: 0 14px 28px rgba(15, 23, 42, 0.12);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    overflow: visible;
}
.node-card-pill {
    position: absolute;
    top: 0;
    left: 22px;
    width: 36px;
    height: 6px;
    border-radius: 999px;
    background: var(--node-accent, var(--flow-accent));
    transform: translateY(-11px);
}
.node-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
}
.node-card-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    background: var(--node-accent-soft, rgba(13, 110, 253, 0.15));
    color: var(--node-accent, var(--flow-accent));
}
.node-card-text {
    display: flex;
    flex-direction: column;
    gap: 4px;
    text-align: left;
}
.node-card-title {
    font-weight: 600;
    font-size: 14px;
    color: #0f172a;
    margin: 0;
}
.node-card-subtitle {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--flow-text-muted);
}
.node-card-meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #94a3b8;
}
.node-card-meta-tag {
    color: #1f2937;
    font-weight: 600;
}
.node-card-meta-state {
    font-weight: 600;
}
.node-card-meta-state.is-configured {
    color: #16a34a;
}
.node-card-meta-state.needs-setup {
    color: #dc2626;
}
.node-card-actions {
    position: absolute;
    top: -34px;
    right: 20px;
    display: flex;
    gap: 6px;
    opacity: 0;
    transform: translateY(-6px);
    transition: opacity 0.2s ease, transform 0.2s ease;
    z-index: 3;
    pointer-events: none;
}
.node-card-actions.is-visible,
.react-flow__node:hover .node-card-actions,
.react-flow__node.selected .node-card-actions {
    opacity: 1;
    transform: translateY(0);
    pointer-events: auto;
}
.node-action-btn {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    border: none;
    background: rgba(15, 23, 42, 0.08);
    color: #0f172a;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s ease, color 0.2s ease;
}
.node-action-btn:hover {
    background: rgba(13, 110, 253, 0.15);
    color: var(--node-accent, var(--flow-accent));
}
.node-action-btn.node-action-danger:hover {
    background: rgba(248, 113, 113, 0.18);
    color: #dc2626;
}
.node-handle {
    opacity: 0;
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.react-flow__node:hover .node-handle,
.react-flow__node.selected .node-handle,
.node-handle.node-handle--connecting {
    opacity: 1;
}
.node-handle-top {
    transform: translateY(-4px);
}
.node-handle-bottom {
    transform: translateY(4px);
}
.node-handle-branch {
    transform: translate(-50%, 4px);
}
.node-handle-true {
    left: 28% !important;
}
.node-handle-false {
    left: 72% !important;
}
.node-handle-legacy {
    pointer-events: none !important;
}
.node-branch-labels {
    position: absolute;
    bottom: -28px;
    left: 0;
    width: 100%;
    display: flex;
    justify-content: space-between;
    padding: 0 24%;
    pointer-events: none;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #94a3b8;
}
.branch-label {
    background: rgba(148, 163, 184, 0.18);
    padding: 2px 8px;
    border-radius: 999px;
}
.react-flow__node:hover .node-card,
.react-flow__node.selected .node-card {
    transform: translateY(-2px);
    box-shadow: var(--flow-shadow);
}
.react-flow__node.selected .node-card {
    border-color: var(--node-accent, var(--flow-accent));
}
.react-flow__edge-path {
    stroke-width: 2;
}
.react-flow__edge-path.selected {
    stroke: var(--flow-accent);
}
.react-flow__controls {
    bottom: 22px;
    left: 22px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.18);
    background: rgba(15, 23, 42, 0.75);
}
.react-flow__controls button {
    border: none;
    background: transparent;
    color: #e2e8f0;
}
.react-flow__controls button:hover {
    background: rgba(255, 255, 255, 0.1);
}
.react-flow__minimap {
    bottom: 22px;
    right: 22px;
    border-radius: 12px;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.18);
    background: rgba(15, 23, 42, 0.78);
}
.form-group label {
    font-weight: 600;
    color: #111827;
    margin-bottom: 5px;
}
.form-control-sm {
    font-size: 0.9rem;
}
.config-section {
    margin-bottom: 20px;
    padding: 18px;
    border: 1px solid rgba(148, 163, 184, 0.24);
    border-radius: 10px;
    background: #f8fafc;
}
.config-section h6 {
    margin: 0 0 14px 0;
    color: #111827;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.02em;
}
.api-trigger-preview {
    display: block;
    font-size: 12px;
    color: var(--flow-text-muted);
}
.node-context-menu {
    position: fixed;
    display: none;
    flex-direction: column;
    min-width: 200px;
    background: rgba(15, 23, 42, 0.95);
    color: #e2e8f0;
    border-radius: 14px;
    padding: 6px 0;
    box-shadow: 0 18px 44px rgba(15, 23, 42, 0.35);
    backdrop-filter: blur(8px);
    z-index: 1200;
}
.node-context-menu.is-visible {
    display: flex;
}
.context-menu-item {
    width: 100%;
    background: transparent;
    border: none;
    color: inherit;
    text-align: left;
    padding: 10px 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    cursor: pointer;
    transition: background 0.18s ease, color 0.18s ease;
}
.context-menu-item:hover,
.context-menu-item:focus {
    background: rgba(255, 255, 255, 0.08);
    outline: none;
}
.context-menu-item i {
    width: 16px;
    text-align: center;
}
.context-menu-divider {
    margin: 4px 0;
    height: 1px;
    background: rgba(148, 163, 184, 0.25);
}
@media (max-width: 1200px) {
    .flow-builder-body {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .flow-builder-sidebar,
    .flow-builder-stage,
    .flow-builder-inspector {
        width: 100%;
    }
    .component-items {
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    }
}
@media (max-width: 991px) {
    .flow-builder-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 18px;
    }
    .header-actions {
        width: 100%;
        flex-wrap: wrap;
        justify-content: flex-start;
    }
    .header-actions .btn-pill {
        flex: 1 1 auto;
    }
    .flow-canvas {
        min-height: 440px;
        height: auto;
    }
    .canvas-floating-controls {
        left: 12px;
        bottom: 12px;
    }
}
@media (max-width: 575px) {
    .component-items {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    }
    .component-item {
        min-height: 64px;
    }
    .btn-pill {
        width: 100%;
        justify-content: center;
    }
}
.response-variable-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.response-variable-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(13, 110, 253, 0.1);
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 12px;
    color: #0d6efd;
    border: 1px solid rgba(13, 110, 253, 0.2);
}
.response-variable-chip i {
    color: rgba(13, 110, 253, 0.65);
}
.response-variable-card {
    border: 1px solid rgba(148, 163, 184, 0.22);
    border-radius: 16px;
    padding: 18px;
    background: linear-gradient(180deg, rgba(248, 250, 252, 0.95) 0%, #ffffff 40%);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04);
}
.response-variable-row {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    align-items: flex-start;
}
.response-variable-row.align-items-stretch {
    align-items: stretch;
}
.response-variable-card .btn-link {
    align-self: center;
}
.response-variable-card .form-group {
    flex: 1 1 220px;
    margin-bottom: 0;
}
.response-variable-card .form-group-source {
    max-width: 180px;
}
.response-variable-card .form-group-source select {
    min-width: 150px;
}
.response-variables-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin-top: 16px;
}
.response-variable-empty {
    text-align: center;
    padding: 24px;
    border: 1px dashed rgba(148, 163, 184, 0.4);
    border-radius: 12px;
    background: rgba(241, 245, 249, 0.35);
    color: #475569;
}
.sample-value-container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-width: 200px;
    background: rgba(15, 23, 42, 0.03);
    border-radius: 12px;
    border: 1px solid rgba(148, 163, 184, 0.3);
    padding: 10px 14px;
    gap: 6px;
}
.sample-label {
    font-size: 11px;
    text-transform: uppercase;
    color: #94a3b8;
    letter-spacing: 0.06em;
}
.sample-value code {
    background: rgba(15, 23, 42, 0.06);
    padding: 4px 6px;
    border-radius: 6px;
    display: inline-block;
}
.sample-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    background: rgba(79, 70, 229, 0.12);
    color: #4338ca;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 500;
}
.sample-empty {
    color: #94a3b8;
    font-size: 12px;
}
.response-variable-panel {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid rgba(148, 163, 184, 0.35);
    padding: 20px;
    margin-top: 10px;
}
.response-variable-panel-header {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.response-variable-panel-header button {
    align-self: flex-start;
}
.status-routing-options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 12px;
    transition: opacity 0.2s ease;
}
.status-routing-options.is-disabled {
    opacity: 0.5;
}
.status-option .custom-control-label {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 10px;
    width: 100%;
    padding: 10px 12px;
    border: 1px solid rgba(148, 163, 184, 0.2);
    border-radius: 12px;
    background: rgba(241, 245, 249, 0.4);
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.status-option .custom-control-input:checked ~ .custom-control-label {
    border-color: rgba(13, 110, 253, 0.45);
    background: rgba(13, 110, 253, 0.1);
    box-shadow: inset 0 0 0 1px rgba(13, 110, 253, 0.15);
}
.status-option .status-code {
    font-family: "Fira Code", "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
    font-weight: 600;
    color: #0f172a;
}
.status-option .status-label {
    font-size: 12px;
    color: #64748b;
}
.status-custom-entry {
    margin-top: 16px;
}
.status-custom-entry label {
    font-size: 12px;
    text-transform: uppercase;
    color: #94a3b8;
    letter-spacing: 0.04em;
    margin-bottom: 4px;
}
.status-summary-label {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.status-summary-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 8px;
}
.status-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    background: rgba(13, 110, 253, 0.12);
    color: #0d6efd;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    border: 1px solid rgba(13, 110, 253, 0.2);
}
.status-chip.muted {
    background: rgba(148, 163, 184, 0.12);
    color: #64748b;
    border-color: rgba(148, 163, 184, 0.25);
}
.condition-config {
    display: grid;
    gap: 18px;
}
@media (min-width: 992px) {
    .condition-config {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .condition-config .config-card.response-card {
        grid-column: span 2;
    }
}
.operator-pill-group {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 10px;
}
.operator-pill {
    border: 1px solid rgba(148, 163, 184, 0.35);
    background: rgba(241, 245, 249, 0.6);
    border-radius: 12px;
    padding: 10px 12px;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s ease;
}
.operator-pill:hover {
    border-color: rgba(99, 102, 241, 0.45);
    background: rgba(99, 102, 241, 0.1);
    color: #4338ca;
}
.operator-pill.is-active {
    background: rgba(99, 102, 241, 0.18);
    border-color: rgba(99, 102, 241, 0.55);
    color: #3730a3;
    box-shadow: inset 0 0 0 1px rgba(99, 102, 241, 0.25);
}
.condition-quick-values {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 10px;
}
.quick-value-label {
    font-size: 12px;
    font-weight: 600;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-right: 6px;
}
.quick-value-chip {
    border: 1px solid rgba(59, 130, 246, 0.3);
    background: rgba(59, 130, 246, 0.12);
    color: #1d4ed8;
    border-radius: 999px;
    padding: 4px 12px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.quick-value-chip:hover {
    background: rgba(59, 130, 246, 0.22);
    border-color: rgba(59, 130, 246, 0.45);
}
.api-trigger-config {
    display: grid;
    gap: 18px;
}
@media (min-width: 992px) {
    .api-trigger-config {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .api-trigger-config .span-2 {
        grid-column: span 2;
    }
}
.config-card {
    background: #ffffff;
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    min-height: 100%;
    display: flex;
    flex-direction: column;
}
.config-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 32px rgba(15, 23, 42, 0.12);
}
.config-card-heading {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    margin-bottom: 16px;
}
.config-card-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    background: rgba(99, 102, 241, 0.12);
    color: #4338ca;
}
.accent-card .config-card-icon {
    background: rgba(13, 110, 253, 0.12);
    color: #0d6efd;
}
.neutral-card .config-card-icon {
    background: rgba(45, 212, 191, 0.12);
    color: #0f766e;
}
.response-card .config-card-icon {
    background: rgba(139, 92, 246, 0.12);
    color: #6d28d9;
}
.config-card-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #0f172a;
}
.config-card-subtitle {
    margin: 2px 0 0;
    font-size: 13px;
    color: #64748b;
}
.config-card-body .form-group:last-child {
    margin-bottom: 0;
}
.api-test-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}
.api-test-note {
    font-size: 13px;
    color: #64748b;
    max-width: 360px;
    flex: 1 1 220px;
}
.api-test-results #test-status {
    border-radius: 12px;
}
.response-variable-manager-card {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.response-variable-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.response-variable-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.response-variable-summary:empty::after {
    content: 'No response variables configured yet. Add a variable below to get started.';
    display: block;
    font-size: 13px;
    color: #94a3b8;
    padding: 6px 8px;
    background: rgba(241, 245, 249, 0.6);
    border-radius: 10px;
}
.response-variable-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f1f5f9;
    border-radius: 999px;
    padding: 6px 12px;
    font-size: 13px;
    color: #0f172a;
    border: 1px solid rgba(148, 163, 184, 0.35);
}
.response-variable-chip i {
    color: #6366f1;
}
.btn-manage-variables {
    white-space: nowrap;
}
.btn-with-icon {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-with-icon i {
    margin-right: 0;
}
.btn-with-icon span {
    display: inline-block;
}
.alert-leading-icon {
    margin-right: 6px;
}
</style>

<script>
const { useCallback, useEffect, useState, useRef } = React;
const ReactFlowLib = window.ReactFlow || {};
const {
    ReactFlow: ReactFlowComponent,
    MiniMap,
    Controls,
    Background,
    useNodesState,
    useEdgesState,
    addEdge,
    MarkerType,
    Handle,
    Position
} = ReactFlowLib;
const FlowHandleComponent = Handle || (ReactFlowLib && ReactFlowLib.Handle) || null;
const FlowPosition = Position || (ReactFlowLib && ReactFlowLib.Position) || null;
let reactFlowInstance;
const FLOW_BUILDER_HOME_URL = <?php echo json_encode(admin_url('flow_builder')); ?>;
// Initial nodes and edges
const initialNodes = [];
const initialEdges = [];
const NODE_STATUS_CONFIGURED = <?php echo json_encode(_l('flow_node_configured')); ?>;
const NODE_STATUS_NEEDS_SETUP = <?php echo json_encode(_l('flow_node_needs_setup')); ?>;
const NODE_ACTION_LABELS = {
    configure: <?php echo json_encode(_l('flow_node_action_configure')); ?>,
    duplicate: <?php echo json_encode(_l('flow_node_action_duplicate')); ?>,
    delete: <?php echo json_encode(_l('flow_node_action_delete')); ?>
};
const nodeThemes = {
    api_trigger: {
        accent: '#0d6efd',
        background: 'linear-gradient(140deg, rgba(13, 110, 253, 0.18) 0%, rgba(13, 110, 253, 0.08) 40%, rgba(13, 110, 253, 0.05) 100%)',
        border: 'rgba(13, 110, 253, 0.45)',
        icon: 'fa fa-project-diagram',
        subtitle: 'Trigger'
    },
    condition: {
        accent: '#28a745',
        background: 'linear-gradient(140deg, rgba(40, 167, 69, 0.18) 0%, rgba(40, 167, 69, 0.06) 60%, rgba(40, 167, 69, 0.04) 100%)',
        border: 'rgba(40, 167, 69, 0.45)',
        icon: 'fa fa-code-branch',
        subtitle: 'Condition'
    },
    staff_create: {
        accent: '#17a2b8',
        background: 'linear-gradient(140deg, rgba(23, 162, 184, 0.18) 0%, rgba(23, 162, 184, 0.05) 100%)',
        border: 'rgba(23, 162, 184, 0.45)',
        icon: 'fa fa-user-plus',
        subtitle: 'Staff Action'
    },
    staff_update: {
        accent: '#17a2b8',
        background: 'linear-gradient(140deg, rgba(23, 162, 184, 0.18) 0%, rgba(23, 162, 184, 0.05) 100%)',
        border: 'rgba(23, 162, 184, 0.45)',
        icon: 'fa fa-user-edit',
        subtitle: 'Staff Action'
    },
    ticket_create: {
        accent: '#ffc107',
        background: 'linear-gradient(140deg, rgba(255, 193, 7, 0.22) 0%, rgba(255, 193, 7, 0.08) 55%, rgba(255, 193, 7, 0.05) 100%)',
        border: 'rgba(255, 193, 7, 0.45)',
        icon: 'fa fa-ticket-alt',
        subtitle: 'Ticket Action'
    },
    ticket_update: {
        accent: '#ffc107',
        background: 'linear-gradient(140deg, rgba(255, 193, 7, 0.22) 0%, rgba(255, 193, 7, 0.08) 55%, rgba(255, 193, 7, 0.05) 100%)',
        border: 'rgba(255, 193, 7, 0.45)',
        icon: 'fa fa-edit',
        subtitle: 'Ticket Action'
    },
    division_create: {
        accent: '#6f42c1',
        background: 'linear-gradient(140deg, rgba(111, 66, 193, 0.22) 0%, rgba(111, 66, 193, 0.07) 60%, rgba(111, 66, 193, 0.05) 100%)',
        border: 'rgba(111, 66, 193, 0.45)',
        icon: 'fa fa-building',
        subtitle: 'Division Action'
    },
    division_update: {
        accent: '#6f42c1',
        background: 'linear-gradient(140deg, rgba(111, 66, 193, 0.22) 0%, rgba(111, 66, 193, 0.07) 60%, rgba(111, 66, 193, 0.05) 100%)',
        border: 'rgba(111, 66, 193, 0.45)',
        icon: 'fa fa-building',
        subtitle: 'Division Action'
    },
    department_create: {
        accent: '#e83e8c',
        background: 'linear-gradient(140deg, rgba(232, 62, 140, 0.22) 0%, rgba(232, 62, 140, 0.08) 55%, rgba(232, 62, 140, 0.05) 100%)',
        border: 'rgba(232, 62, 140, 0.45)',
        icon: 'fa fa-sitemap',
        subtitle: 'Department Action'
    },
    department_update: {
        accent: '#e83e8c',
        background: 'linear-gradient(140deg, rgba(232, 62, 140, 0.22) 0%, rgba(232, 62, 140, 0.08) 55%, rgba(232, 62, 140, 0.05) 100%)',
        border: 'rgba(232, 62, 140, 0.45)',
        icon: 'fa fa-edit',
        subtitle: 'Department Action'
    },
    sms_send: {
        accent: '#20c997',
        background: 'linear-gradient(140deg, rgba(32, 201, 151, 0.22) 0%, rgba(32, 201, 151, 0.08) 55%, rgba(32, 201, 151, 0.05) 100%)',
        border: 'rgba(32, 201, 151, 0.45)',
        icon: 'fa fa-sms',
        subtitle: 'Communication'
    },
    whatsapp_send: {
        accent: '#25d366',
        background: 'linear-gradient(140deg, rgba(37, 211, 102, 0.22) 0%, rgba(37, 211, 102, 0.08) 55%, rgba(37, 211, 102, 0.05) 100%)',
        border: 'rgba(37, 211, 102, 0.45)',
        icon: 'fa fa-whatsapp',
        subtitle: 'Communication'
    },
    email_send: {
        accent: '#dc3545',
        background: 'linear-gradient(140deg, rgba(220, 53, 69, 0.22) 0%, rgba(220, 53, 69, 0.08) 55%, rgba(220, 53, 69, 0.05) 100%)',
        border: 'rgba(220, 53, 69, 0.45)',
        icon: 'fa fa-envelope',
        subtitle: 'Communication'
    },
    default: {
        accent: '#6366f1',
        background: 'linear-gradient(140deg, rgba(99, 102, 241, 0.2) 0%, rgba(99, 102, 241, 0.07) 55%, rgba(99, 102, 241, 0.05) 100%)',
        border: 'rgba(99, 102, 241, 0.42)',
        icon: 'fa fa-cube',
        subtitle: 'Flow Step'
    }
};
function formatNodeTag(type) {
    if (!type || typeof type !== 'string') {
        return 'Flow Step';
    }
    return type.split('_').map(part => part.charAt(0).toUpperCase() + part.slice(1)).join(' ');
}
function getNodeTheme(type) {
    return nodeThemes[type] || nodeThemes.default;
}
function getNodeWrapperStyle() {
    return {
        padding: 0,
        border: 'none',
        background: 'transparent',
        boxShadow: 'none',
        borderRadius: 0
    };
}
function createNodeData(type, name, config) {
    const theme = getNodeTheme(type);
    const displayName = name || formatNodeTag(type);
    const initialConfig = { ...(config || {}) };
    if (type === 'api_trigger') {
        if (!initialConfig.variable_name) {
            initialConfig.variable_name = 'api_response';
        }
    }
    return {
        label: displayName,
        title: displayName,
        subtitle: theme.subtitle,
        tag: formatNodeTag(type),
        icon: theme.icon,
        accent: theme.accent,
        background: theme.background,
        border: theme.border,
        type: type,
        variable_name: initialConfig.variable_name || null,
        external_api_id: initialConfig.external_api_id || null,
        external_api_label: initialConfig.external_api_label || null,
        config: initialConfig
    };
}
function ensureBuilderNode(node) {
    if (!node) {
        return node;
    }

    // Preserve the original node ID if it exists
    const nodeId = node.id || `${node.type || 'node'}_${Date.now()}_${Math.random()}`;

    const nodeType = node.data && node.data.type ? node.data.type : (node.className || node.type || 'default');
    const theme = getNodeTheme(nodeType);
    const existingConfig = node.data && node.data.config ? node.data.config : {};
    const displayName = node.data && (node.data.title || node.data.label) ? (node.data.title || node.data.label) : formatNodeTag(nodeType);

    // Sanitize position to prevent invalid coordinates, but preserve existing valid positions
    let position = node.position;
    if (!position || typeof position.x !== 'number' || isNaN(position.x) || typeof position.y !== 'number' || isNaN(position.y)) {
        position = { x: Math.random() * 300 + 50, y: Math.random() * 300 + 50 };
    }

    // Ensure all required React Flow properties are present
    const enhancedNode = {
        id: nodeId,
        type: 'builderNode',
        position: position,
        className: nodeType,
        style: getNodeWrapperStyle(),
        dragHandle: '.node-card',
        data: {
            ...createNodeData(nodeType, displayName, existingConfig),
            ...node.data
        }
    };

    // Preserve any additional properties that might be needed
    if (node.selected !== undefined) enhancedNode.selected = node.selected;
    if (node.dragging !== undefined) enhancedNode.dragging = node.dragging;
    if (node.width !== undefined) enhancedNode.width = node.width;
    if (node.height !== undefined) enhancedNode.height = node.height;

    return enhancedNode;
}
function syncSelectedNodeReference(nodeList) {
    if (!window.selectedNode || !Array.isArray(nodeList)) {
        return;
    }
    const latest = nodeList.find(n => n && n.id === window.selectedNode.id);
    if (latest) {
        window.selectedNode = latest;
        renderSelectedNodeSummary(latest);
    }
}
function withOpacity(hexColor, opacity) {
    if (!hexColor) {
        return `rgba(99, 102, 241, ${opacity})`;
    }
    let hex = hexColor.replace('#', '');
    if (hex.length === 3) {
        hex = hex.split('').map(x => x + x).join('');
    }
    if (hex.length !== 6) {
        return `rgba(99, 102, 241, ${opacity})`;
    }
    const r = parseInt(hex.substring(0, 2), 16);
    const g = parseInt(hex.substring(2, 4), 16);
    const b = parseInt(hex.substring(4, 6), 16);
    return `rgba(${r}, ${g}, ${b}, ${opacity})`;
}
function hasConfiguration(config, nodeType) {
    if (!config) {
        return false;
    }
    if (nodeType === 'api_trigger') {
        return !!(config.external_api_id);
    }
    if (Array.isArray(config)) {
        return config.length > 0;
    }
    return Object.keys(config).some(key => {
        const value = config[key];
        if (Array.isArray(value)) {
            return value.length > 0;
        }
        return value !== null && value !== undefined && value !== '';
    });
}
function escapeHtml(value) {
    if (value === null || value === undefined) {
        return '';
    }
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}
function enterFlowBuilderFullscreen() {
    const body = document.body;
    if (body) {
        body.classList.add('flow-builder-fullscreen');
        body.classList.remove('show-sidebar');
        body.classList.remove('mobile-menu-active');
    }
    const wrapper = document.getElementById('wrapper');
    if (wrapper && wrapper.dataset.originalMarginLeft === undefined) {
        wrapper.dataset.originalMarginLeft = wrapper.style.marginLeft || '';
        wrapper.style.marginLeft = '0';
    }
    const content = document.getElementById('content');
    if (content && content.dataset.originalMarginLeft === undefined) {
        content.dataset.originalMarginLeft = content.style.marginLeft || '';
        content.style.marginLeft = '0';
    }
    const toggle = document.querySelector('.mobile-menu-toggle');
    if (toggle && toggle.dataset.originalDisplay === undefined) {
        toggle.dataset.originalDisplay = toggle.style.display || '';
        toggle.style.display = 'none';
    }
    ['#setup-menu', '#setup-menu-wrapper', '#side-menu', '#mobile-menu', '#menu', '.mobile-menu', '.mobile-menu-wrapper', '.menu-overlay', '.mobile-menu-overlay'].forEach(selector => {
        document.querySelectorAll(selector).forEach(element => {
            if (element.dataset.originalDisplay === undefined) {
                element.dataset.originalDisplay = element.style.display || '';
            }
            element.style.display = 'none';
        });
    });
}
function exitFlowBuilderFullscreen() {
    stopFlowBuilderLayoutMaintenance();
    ['#setup-menu', '#setup-menu-wrapper', '#side-menu', '#mobile-menu', '#menu', '.mobile-menu', '.mobile-menu-wrapper', '.menu-overlay', '.mobile-menu-overlay'].forEach(selector => {
        document.querySelectorAll(selector).forEach(element => {
            if (element.dataset.originalDisplay !== undefined) {
                element.style.display = element.dataset.originalDisplay;
                delete element.dataset.originalDisplay;
            } else {
                element.style.removeProperty('display');
            }
        });
    });
    const toggle = document.querySelector('.mobile-menu-toggle');
    if (toggle) {
        if (toggle.dataset.originalDisplay !== undefined) {
            toggle.style.display = toggle.dataset.originalDisplay;
            delete toggle.dataset.originalDisplay;
        } else {
            toggle.style.removeProperty('display');
        }
    }
    const wrapper = document.getElementById('wrapper');
    if (wrapper) {
        if (wrapper.dataset.originalMarginLeft !== undefined) {
            wrapper.style.marginLeft = wrapper.dataset.originalMarginLeft;
            delete wrapper.dataset.originalMarginLeft;
        } else {
            wrapper.style.removeProperty('margin-left');
        }
    }
    const content = document.getElementById('content');
    if (content) {
        if (content.dataset.originalMarginLeft !== undefined) {
            content.style.marginLeft = content.dataset.originalMarginLeft;
            delete content.dataset.originalMarginLeft;
        } else {
            content.style.removeProperty('margin-left');
        }
    }
    const body = document.body;
    if (body) {
        body.classList.remove('flow-builder-fullscreen');
        body.classList.remove('mobile-menu-active');
        body.classList.remove('show-sidebar');
    }
}
function navigateBackToFlowList() {
    exitFlowBuilderFullscreen();
    window.location.href = FLOW_BUILDER_HOME_URL;
}
let flowBuilderLayoutInterval = null;
function startFlowBuilderLayoutMaintenance() {
    if (flowBuilderLayoutInterval) {
        clearInterval(flowBuilderLayoutInterval);
    }
    const enforce = function() {
        enterFlowBuilderFullscreen();
    };
    enforce();
    flowBuilderLayoutInterval = setInterval(enforce, 400);
}
function stopFlowBuilderLayoutMaintenance() {
    if (flowBuilderLayoutInterval) {
        clearInterval(flowBuilderLayoutInterval);
        flowBuilderLayoutInterval = null;
    }
}
function bindFlowBuilderBackButtons() {
    [
        document.getElementById('flow-builder-back-button'),
        document.getElementById('flow-builder-back-icon')
    ].forEach(function(button) {
        if (!button || button.dataset.flowBuilderBackBound === 'true') {
            return;
        }
        button.dataset.flowBuilderBackBound = 'true';
        button.addEventListener('click', function(event) {
            if (event && typeof event.preventDefault === 'function') {
                event.preventDefault();
            }
            navigateBackToFlowList();
        });
    });
}
function bindFlowBuilderActionButtons() {
    const actionMap = {
        save: window.saveFlow,
        test: window.testFlow,
        export: window.exportFlow,
        fit: window.fitToScreen
    };
    Object.keys(actionMap).forEach(function(action) {
        const handler = actionMap[action];
        if (typeof handler !== 'function') {
            return;
        }
        document.querySelectorAll('[data-flow-action="' + action + '"]').forEach(function(button) {
            if (!button || button.dataset.flowActionBound === 'true') {
                return;
            }
            button.dataset.flowActionBound = 'true';
            button.addEventListener('click', function(event) {
                if (event && typeof event.preventDefault === 'function') {
                    event.preventDefault();
                }
                handler();
            });
        });
    });
}
function ensureFlowBuilderHeaderBindings() {
    bindFlowBuilderBackButtons();
    bindFlowBuilderActionButtons();
}
function ensureFlowBuilderChromeControl() {
    startFlowBuilderLayoutMaintenance();
}
function renderSelectedNodeSummary(node) {
    const container = document.getElementById('node-inspector-panel');
    if (!container) {
        return;
    }
    if (!node) {
        container.classList.add('inspector-empty');
        container.innerHTML = `
            <div class="inspector-empty-state">
                <span class="inspector-pill">
                    <i class="fa fa-magic"></i>
                </span>
                <h6>Select a step</h6>
                <p>Click a block on the canvas to see its summary, variables, and quick actions here.</p>
            </div>
        `;
        container.dataset.nodeId = '';
        return;
    }
    container.classList.remove('inspector-empty');
    const data = node.data || {};
    const theme = getNodeTheme(data.type);
    const accent = data.accent || theme.accent || '#6366f1';
    const statusAccent = hasConfiguration(data.config, data.type) ? accent : '#ef4444';
    const title = data.title || data.label || formatNodeTag(data.type || '');
    const tag = data.tag || formatNodeTag(data.type || '');
    const configured = hasConfiguration(data.config, data.type);
    const statusLabel = configured ? NODE_STATUS_CONFIGURED : NODE_STATUS_NEEDS_SETUP;
    const variableDisplay = data.variable_name ? `{{${data.variable_name}}}` : '—';
    const infoRows = [
        { label: 'Node ID', value: node.id || '—' },
        { label: 'Variable', value: variableDisplay }
    ];
    if (data.external_api_label) {
        infoRows.push({ label: 'Source', value: data.external_api_label });
    }
    if (data.config && data.config.http_status_routing && data.config.http_status_routing.enabled) {
        const statuses = (data.config.http_status_routing.statuses || []).join(', ') || 'default';
        infoRows.push({ label: 'Status Routes', value: statuses });
    }
    const infoHtml = infoRows.map((row) => {
        const valueText = row.value ? escapeHtml(row.value) : '&mdash;';
        return `
            <li>
                <span>${escapeHtml(row.label)}</span>
                <span>${valueText}</span>
            </li>
        `;
    }).join('');
    const statusStyle = `background:${withOpacity(statusAccent, 0.12)}; color:${statusAccent}; border-color:${withOpacity(statusAccent, 0.25)};`;
    const chipStyle = `background:${withOpacity(accent, 0.12)}; color:${accent}; border-color:${withOpacity(accent, 0.28)};`;
    container.innerHTML = `
        <div class="inspector-node">
            <div class="inspector-node-header">
                <span class="inspector-node-chip" style="${chipStyle}">
                    <i class="${theme.icon || 'fa fa-cube'}"></i>
                </span>
                <div class="inspector-node-titles">
                    <h6 class="inspector-node-title">${escapeHtml(title)}</h6>
                    <span class="inspector-node-type">${escapeHtml(tag)}</span>
                </div>
                <span class="inspector-node-status ${configured ? 'is-configured' : 'needs-setup'}" style="${statusStyle}">${escapeHtml(statusLabel)}</span>
            </div>
            <div class="inspector-node-body">
                <ul class="inspector-node-list">
                    ${infoHtml}
                </ul>
                <p class="inspector-node-hint"><i class="fa fa-mouse-pointer"></i> Double-click the block on the canvas to open configuration.</p>
            </div>
            <div class="inspector-node-actions">
                <button type="button" class="inspector-action" data-action="configure">
                    <i class="fa fa-cog"></i> ${NODE_ACTION_LABELS.configure}
                </button>
                <button type="button" class="inspector-action" data-action="duplicate">
                    <i class="fa fa-clone"></i> ${NODE_ACTION_LABELS.duplicate}
                </button>
                <button type="button" class="inspector-action danger" data-action="delete">
                    <i class="fa fa-trash"></i> ${NODE_ACTION_LABELS.delete}
                </button>
            </div>
        </div>
    `;
    container.dataset.nodeId = node.id || '';
    container.querySelectorAll('[data-action]').forEach((button) => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const action = this.dataset.action;
            if (window.flowBuilderActions && typeof window.flowBuilderActions[action] === 'function') {
                window.flowBuilderActions[action](node.id);
            }
        });
    });
}
const NodeCard = ({ id, data, selected, isConnectable }) => {
    const accent = data && data.accent ? data.accent : nodeThemes.default.accent;
    const nodeDataType = data && data.type ? data.type : undefined;
    const isCondition = nodeDataType === 'condition';
    const hasConfig = hasConfiguration(data ? data.config : null, nodeDataType);
    const statusLabel = hasConfig ? NODE_STATUS_CONFIGURED : NODE_STATUS_NEEDS_SETUP;
    const statusClass = hasConfig ? 'is-configured' : 'needs-setup';
    const tagText = data && data.tag ? data.tag : formatNodeTag(nodeDataType || '');
    const subtitleText = data && data.external_api_label ? data.external_api_label : (data && data.subtitle ? data.subtitle : tagText);
    const variableMeta = data && data.variable_name ? 'var: ' + data.variable_name : tagText;
    const cardStyle = {
        background: data && data.background ? data.background : nodeThemes.default.background,
        borderColor: data && data.border ? data.border : nodeThemes.default.border,
        '--node-accent': accent,
        '--node-border': data && data.border ? data.border : nodeThemes.default.border,
        '--node-accent-soft': withOpacity(accent, 0.18)
    };
    const handleStyle = {
        background: accent,
        border: '2px solid #ffffff',
        width: 12,
        height: 12,
        borderRadius: '50%',
        boxShadow: '0 0 0 2px rgba(15, 23, 42, 0.12)'
    };
    const invokeAction = (action, event) => {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        if (window.flowBuilderActions && typeof window.flowBuilderActions[action] === 'function') {
            window.flowBuilderActions[action](id);
        }
    };
    return React.createElement(React.Fragment, null,
        FlowHandleComponent ? React.createElement(FlowHandleComponent, {
            key: `${id}-target`,
            id: `${id}-target`,
            type: 'target',
            position: FlowPosition ? FlowPosition.Top : 'top',
            style: { ...handleStyle },
            className: 'node-handle node-handle-top',
            isConnectable: isConnectable
        }) : null,
        !isCondition && FlowHandleComponent ? React.createElement(FlowHandleComponent, {
            key: `${id}-source`,
            id: `${id}-source`,
            type: 'source',
            position: FlowPosition ? FlowPosition.Bottom : 'bottom',
            style: { ...handleStyle },
            className: 'node-handle node-handle-bottom',
            isConnectable: isConnectable
        }) : null,
        isCondition && FlowHandleComponent ? React.createElement(React.Fragment, null,
            React.createElement(FlowHandleComponent, {
                key: `${id}-true`,
                id: 'true',
                type: 'source',
                position: FlowPosition ? FlowPosition.Bottom : 'bottom',
                style: { ...handleStyle, left: '28%' },
                className: 'node-handle node-handle-bottom node-handle-branch node-handle-true',
                isConnectable: isConnectable
            }),
            React.createElement(FlowHandleComponent, {
                key: `${id}-false`,
                id: 'false',
                type: 'source',
                position: FlowPosition ? FlowPosition.Bottom : 'bottom',
                style: { ...handleStyle, left: '72%' },
                className: 'node-handle node-handle-bottom node-handle-branch node-handle-false',
                isConnectable: isConnectable
            }),
            React.createElement(FlowHandleComponent, {
                key: `${id}-legacy`,
                id: `${id}-source`,
                type: 'source',
                position: FlowPosition ? FlowPosition.Bottom : 'bottom',
                style: { ...handleStyle, opacity: 0, pointerEvents: 'none' },
                className: 'node-handle node-handle-bottom node-handle-legacy',
                isConnectable: false
            })
        ) : null,
        React.createElement('div', { className: 'node-card', style: cardStyle },
        React.createElement('div', { className: `node-card-actions ${selected ? 'is-visible' : ''}` },
            React.createElement('button', {
                type: 'button',
                className: 'node-action-btn',
                title: NODE_ACTION_LABELS.configure,
                onClick: (event) => invokeAction('configure', event)
            }, React.createElement('i', { className: 'fa fa-cog', 'aria-hidden': true })),
            React.createElement('button', {
                type: 'button',
                className: 'node-action-btn',
                title: NODE_ACTION_LABELS.duplicate,
                onClick: (event) => invokeAction('duplicate', event)
            }, React.createElement('i', { className: 'fa fa-clone', 'aria-hidden': true })),
            React.createElement('button', {
                type: 'button',
                className: 'node-action-btn node-action-danger',
                title: NODE_ACTION_LABELS.delete,
                onClick: (event) => invokeAction('delete', event)
            }, React.createElement('i', { className: 'fa fa-trash', 'aria-hidden': true }))
        ),
        React.createElement('span', { className: 'node-card-pill' }),
            React.createElement('div', { className: 'node-card-header' },
            React.createElement('span', {
                className: 'node-card-icon',
                style: { background: withOpacity(accent, 0.16), color: accent }
            }, data && data.icon ? React.createElement('i', { className: data.icon, 'aria-hidden': true }) : null),
            React.createElement('div', { className: 'node-card-text' },
                React.createElement('span', { className: 'node-card-title' }, data && (data.title || data.label) ? (data.title || data.label) : 'Flow Step'),
                React.createElement('span', { className: 'node-card-subtitle' }, subtitleText)
            )
        ),
        React.createElement('div', { className: 'node-card-meta' },
            React.createElement('span', { className: 'node-card-meta-tag' }, variableMeta),
            React.createElement('span', { className: `node-card-meta-state ${statusClass}` }, statusLabel)
        ),
        isCondition ? React.createElement('div', { className: 'node-branch-labels' },
            React.createElement('span', { className: 'branch-label branch-label-true' }, 'TRUE'),
            React.createElement('span', { className: 'branch-label branch-label-false' }, 'FALSE')
        ) : null
    ));
};
const nodeTypes = {
    builderNode: NodeCard
};

const EXISTING_FLOW_ID = <?php echo isset($flow_id) && $flow_id ? (int) $flow_id : 'null'; ?>;
const FLOW_DETAILS_INITIAL = <?php echo json_encode([
    'name' => isset($flow) && isset($flow['name']) ? $flow['name'] : '',
    'description' => isset($flow) && isset($flow['description']) ? $flow['description'] : ''
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
const FLOW_DETAILS_TEXT = <?php echo json_encode([
    'heading' => _l('flow_details_heading'),
    'subheading' => _l('flow_details_subheading'),
    'name_label' => _l('flow_name'),
    'description_label' => _l('flow_description'),
    'name_placeholder' => _l('flow_name_placeholder'),
    'description_placeholder' => _l('flow_description_placeholder')
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

const flowDetailsStore = (function(initial) {
    const baseState = {
        name: '',
        description: ''
    };
    const listeners = new Set();
    const normalize = (data = {}) => {
        const normalized = {};
        if (Object.prototype.hasOwnProperty.call(data, 'name')) {
            normalized.name = data.name !== undefined && data.name !== null ? String(data.name) : '';
        }
        if (Object.prototype.hasOwnProperty.call(data, 'description')) {
            normalized.description = data.description !== undefined && data.description !== null ? String(data.description) : '';
        }
        return normalized;
    };
    let state = Object.assign({}, baseState, normalize(initial));
    const emit = () => {
        const snapshot = Object.assign({}, state);
        listeners.forEach(listener => {
            try {
                listener(snapshot);
            } catch (error) {
                console.error('Flow details listener error:', error);
            }
        });
    };
    return {
        get() {
            return Object.assign({}, state);
        },
        set(updates) {
            if (!updates || typeof updates !== 'object') {
                return;
            }
            const normalized = normalize(updates);
            if (Object.keys(normalized).length === 0) {
                return;
            }
            state = Object.assign({}, state, normalized);
            emit();
        },
        subscribe(listener) {
            if (typeof listener !== 'function') {
                return () => {};
            }
            listeners.add(listener);
            listener(Object.assign({}, state));
            return () => listeners.delete(listener);
        }
    };
})(FLOW_DETAILS_INITIAL || {});

window.getFlowDetails = function() {
    return flowDetailsStore.get();
};
window.setFlowDetails = function(values) {
    flowDetailsStore.set(values || {});
};

function FlowDetailsPanel(props) {
    const ReactGlobal = window.React;
    if (!ReactGlobal) {
        return null;
    }
    const { useState, useEffect } = ReactGlobal;
    const text = props && props.text ? props.text : {};
    const [form, setForm] = useState(flowDetailsStore.get());

    useEffect(() => {
        const unsubscribe = flowDetailsStore.subscribe(setForm);
        return unsubscribe;
    }, []);

    const handleChange = (field) => (event) => {
        flowDetailsStore.set({ [field]: event.target.value });
    };
    const handleNameChange = handleChange('name');
    const handleDescriptionChange = handleChange('description');

    return React.createElement('div', {
        className: 'flow-details-card'
    },
        React.createElement('div', { className: 'flow-details-header' },
            React.createElement('span', { className: 'flow-details-icon' },
                React.createElement('i', { className: 'fa fa-cogs', 'aria-hidden': true })
            ),
            React.createElement('div', { className: 'flow-details-titles' },
                React.createElement('h5', { className: 'flow-details-heading' }, text.heading || 'Flow Settings'),
                React.createElement('p', { className: 'flow-details-subtitle' }, text.subheading || 'Give your automation a clear name and context so teammates instantly understand its purpose.')
            )
        ),
        React.createElement('div', { className: 'flow-details-grid' },
            React.createElement('div', { className: 'flow-details-field' },
                React.createElement('label', { className: 'flow-details-label', htmlFor: 'flow-name' },
                    text.name_label || 'Flow Name',
                    React.createElement('span', { className: 'flow-details-required' }, '*')
                ),
                React.createElement('input', {
                    type: 'text',
                    id: 'flow-name',
                    className: 'flow-details-input',
                    value: form.name || '',
                    placeholder: text.name_placeholder || 'Give this journey a clear, memorable name',
                    onChange: handleNameChange,
                    onInput: handleNameChange
                })
            ),
            React.createElement('div', { className: 'flow-details-field' },
                React.createElement('label', { className: 'flow-details-label', htmlFor: 'flow-description' },
                    text.description_label || 'Description'
                ),
                React.createElement('textarea', {
                    id: 'flow-description',
                    className: 'flow-details-textarea',
                    rows: 3,
                    value: form.description || '',
                    placeholder: text.description_placeholder || 'Outline the purpose, entry conditions, or key outcomes for this flow',
                    onChange: handleDescriptionChange,
                    onInput: handleDescriptionChange
                })
            )
        ),
        React.createElement('div', { className: 'flow-details-footer' },
            React.createElement('span', { className: 'flow-details-status' },
                React.createElement('i', { className: 'fa fa-check-circle', 'aria-hidden': true }),
                text.autosave_label || 'Auto-save enabled'
            ),
            React.createElement('span', { className: 'flow-details-meta' },
                text.last_saved_label || 'Last saved',
                ' - ',
                React.createElement('span', { id: 'last-saved-time' }, 'just now')
            )
        )
    );
}


function FlowBuilderCanvas() {
    const [nodes, setNodes, onNodesChange] = useNodesState(initialNodes);
    const [edges, setEdges, onEdgesChange] = useEdgesState(initialEdges);
    const [selectedNode, setSelectedNode] = useState(null);
    const reactFlowWrapper = useRef(null);
    const instanceRef = useRef(null);
    const contextMenuRef = useRef(null);
    const selectedNodeRef = useRef(null);
    const syncCanvasState = useCallback((nodesList) => {
        const canvasEl = document.getElementById('flow-canvas');
        if (canvasEl) {
            canvasEl.dataset.hasNodes = nodesList && nodesList.length > 0 ? 'true' : 'false';
        }
    }, []);
    const deleteNodeById = useCallback((nodeId) => {
        if (!nodeId) {
            return;
        }
        setNodes((nds) => {
            const updated = nds.filter(n => n.id !== nodeId);
            window.nodes = updated;
            syncCanvasState(updated);
            return updated;
        });
        setEdges((eds) => {
            const updated = eds.filter(edge => edge.source !== nodeId && edge.target !== nodeId);
            window.edges = updated;
            return updated;
        });
        if (selectedNodeRef.current && selectedNodeRef.current.id === nodeId) {
            selectedNodeRef.current = null;
            setSelectedNode(null);
        }
    }, [setNodes, setEdges, syncCanvasState]);
    const duplicateNodeById = useCallback((nodeId) => {
        if (!nodeId) {
            return;
        }
        let createdNode = null;
        setNodes((nds) => {
            const original = nds.find(n => n.id === nodeId);
            if (!original) {
                return nds;
            }
            const nodeType = (original.data && original.data.type) || original.className || 'default';
            const displayName = original.data && (original.data.title || original.data.label)
                ? (original.data.title || original.data.label)
                : formatNodeTag(nodeType);
            const clonedConfig = original.data && original.data.config ? JSON.parse(JSON.stringify(original.data.config)) : {};
            const duplicateNode = {
                id: `${nodeType}_${Date.now()}`,
                type: 'builderNode',
                position: {
                    x: (original.position && typeof original.position.x === 'number') ? original.position.x + 48 : 150,
                    y: (original.position && typeof original.position.y === 'number') ? original.position.y + 48 : 150
                },
                data: createNodeData(nodeType, displayName, clonedConfig),
                className: nodeType,
                dragHandle: '.node-card',
                style: getNodeWrapperStyle()
            };
            createdNode = duplicateNode;
            const updated = nds.concat(duplicateNode);
            window.nodes = updated;
            syncCanvasState(updated);
            return updated;
        });
        if (createdNode) {
            setSelectedNode(createdNode);
            selectedNodeRef.current = createdNode;
        }
    }, [setNodes, syncCanvasState]);
    const configureNodeById = useCallback((nodeId) => {
        if (!nodeId) {
            return;
        }
        const target = (nodes || []).find(n => n.id === nodeId);
        if (target) {
            setSelectedNode(target);
            selectedNodeRef.current = target;
            showNodeConfiguration(target);
        }
    }, [nodes]);
    const hideContextMenu = useCallback(() => {
        const menu = contextMenuRef.current;
        if (menu) {
            menu.classList.remove('is-visible');
            menu.style.visibility = '';
            menu.dataset.nodeId = '';
        }
    }, []);
    const convertScreenToFlow = useCallback((point, options = {}) => {
        const instance = instanceRef.current;
        if (!instance || !point) {
            return point;
        }
        try {
            if (typeof instance.screenToFlowPosition === 'function') {
                return instance.screenToFlowPosition(point);
            }
            const bounds = options.bounds || (reactFlowWrapper.current ? reactFlowWrapper.current.getBoundingClientRect() : null);
            const relativePoint = bounds
                ? { x: point.x - bounds.left, y: point.y - bounds.top }
                : point;
            if (typeof instance.project === 'function') {
                return instance.project(relativePoint);
            }
            return relativePoint;
        } catch (error) {
            console.error('Error converting screen position to flow position:', error);
        }
        return point;
    }, []);
    const handleContextAction = useCallback((action) => {
        const node = selectedNodeRef.current;
        if (!node) {
            hideContextMenu();
            return;
        }
        switch (action) {
            case 'configure':
                configureNodeById(node.id);
                break;
            case 'duplicate':
                duplicateNodeById(node.id);
                break;
            case 'delete':
                deleteNodeById(node.id);
                break;
            default:
                break;
        }
        hideContextMenu();
    }, [configureNodeById, duplicateNodeById, deleteNodeById, hideContextMenu]);
    const addNodeToCanvas = useCallback((componentType, componentName, flowPosition) => {
        if (!componentType) {
            return;
        }
        let position = flowPosition;
        if (!position && instanceRef.current && reactFlowWrapper.current) {
            const bounds = reactFlowWrapper.current.getBoundingClientRect();
            try {
                position = convertScreenToFlow({
                    x: bounds.left + bounds.width / 2,
                    y: bounds.top + bounds.height / 2
                }, { bounds });
            } catch (error) {
                console.error('Error determining default position:', error);
            }
        }
        if (!position) {
            position = { x: 150, y: 150 };
        }
        const newNode = {
            id: `${componentType}_${Date.now()}`,
            type: 'builderNode',
            position,
            data: createNodeData(componentType, componentName, {}),
            className: componentType,
            dragHandle: '.node-card',
            style: getNodeWrapperStyle()
        };
        setNodes((nds) => {
            const newNodes = nds.concat(newNode);
            syncCanvasState(newNodes);
            window.nodes = newNodes;
            return newNodes;
        });
    }, [setNodes, convertScreenToFlow, syncCanvasState]);
    const onConnect = useCallback((params) => {
        const markerEnd = (MarkerType && MarkerType.ArrowClosed) ? { type: MarkerType.ArrowClosed } : undefined;
        const edge = {
            ...params,
            type: 'smoothstep',
            markerEnd,
            style: { stroke: '#555' }
        };
        setEdges((eds) => {
            const newEdges = addEdge(edge, eds);
            window.edges = newEdges; // Update global reference
            return newEdges;
        });
    }, []);
    const onDragOver = useCallback((event) => {
        event.preventDefault();
        event.dataTransfer.dropEffect = 'move';
    }, []);
    const onInit = useCallback((instance) => {
        console.log('React Flow initialized');
        instanceRef.current = instance;
        reactFlowInstance = instance;

        // Make setNodes and setEdges available globally
        window.setNodes = setNodes;
        window.setEdges = setEdges;
        window.getReactFlowInstance = () => instanceRef.current;

        syncCanvasState([]);

        // Handle pending flow data if available
        if (window.__pendingFlowData) {
            console.log('Processing pending flow data:', window.__pendingFlowData);
            const pending = window.__pendingFlowData;
            delete window.__pendingFlowData;

            if (pending.nodes && pending.nodes.length > 0) {
                const enhancedNodes = pending.nodes.map(node => {
                    const enhanced = ensureBuilderNode(node);
                    console.log('Enhanced pending node:', enhanced.id, enhanced);
                    return enhanced;
                });
                console.log('Setting enhanced nodes:', enhancedNodes.length);
                setNodes(enhancedNodes);
                syncCanvasState(enhancedNodes);
            }

            if (pending.edges && pending.edges.length > 0) {
                const enhancedEdges = pending.edges.map(edge => ({
                    ...edge,
                    id: edge.id || `edge-${edge.source}-${edge.target}`,
                    type: edge.type || 'smoothstep',
                    style: edge.style || { stroke: '#555' }
                }));
                console.log('Setting enhanced edges:', enhancedEdges.length);
                setEdges(enhancedEdges);
            }
        }

        // Fit view after a short delay to ensure all nodes are rendered
        setTimeout(() => {
            if (instance && typeof instance.fitView === 'function') {
                console.log('Fitting view to nodes');
                instance.fitView({
                    padding: 0.1,
                    includeHiddenNodes: false,
                    minZoom: 0.1,
                    maxZoom: 1.5
                });
            }
        }, 150);

        // Also fit view when nodes change
        const handleNodesChange = () => {
            setTimeout(() => {
                if (instance && typeof instance.fitView === 'function') {
                    const currentNodes = instance.getNodes();
                    if (currentNodes && currentNodes.length > 0) {
                        instance.fitView({
                            padding: 0.1,
                            includeHiddenNodes: false,
                            minZoom: 0.1,
                            maxZoom: 1.5
                        });
                    }
                }
            }, 100);
        };

        // Listen for node changes to refit view
        if (instance && typeof instance.on === 'function') {
            instance.on('nodeschange', handleNodesChange);
        }
    }, [setNodes, setEdges, syncCanvasState]);
    const onDrop = useCallback((event) => {
        event.preventDefault();
        const componentType = event.dataTransfer.getData('application/reactflow/type');
        const componentName = event.dataTransfer.getData('application/reactflow/name');
        if (typeof componentType === 'undefined' || !componentType) {
            return;
        }
        const bounds = reactFlowWrapper.current ? reactFlowWrapper.current.getBoundingClientRect() : null;
        let position = null;
        if (instanceRef.current) {
            try {
                position = convertScreenToFlow({
                    x: event.clientX,
                    y: event.clientY
                }, { bounds });
            } catch (error) {
                console.error('Error converting position:', error);
            }
        }
        if (!position && bounds) {
            position = {
                x: event.clientX - bounds.left,
                y: event.clientY - bounds.top
            };
        }
        if (!position) {
            position = { x: event.clientX, y: event.clientY };
        }
        addNodeToCanvas(componentType, componentName, position);
    }, [addNodeToCanvas, convertScreenToFlow]);
    const onNodeClick = useCallback((event, node) => {
        setSelectedNode(node);
        selectedNodeRef.current = node;
        hideContextMenu();
    }, [hideContextMenu]);
    const onNodeDoubleClick = useCallback((event, node) => {
        event.preventDefault();
        selectedNodeRef.current = node;
        setSelectedNode(node);
        hideContextMenu();
        showNodeConfiguration(node);
    }, [hideContextMenu]);
    const onNodeContextMenu = useCallback((event, node) => {
        event.preventDefault();
        const menu = contextMenuRef.current;
        if (!menu) {
            return;
        }
        selectedNodeRef.current = node;
        setSelectedNode(node);
        hideContextMenu();
        menu.dataset.nodeId = node.id;
        menu.style.visibility = 'hidden';
        menu.classList.add('is-visible');
        menu.style.top = '0px';
        menu.style.left = '0px';
        requestAnimationFrame(() => {
            const rect = menu.getBoundingClientRect();
            const padding = 12;
            let top = event.clientY;
            let left = event.clientX;
            if (top + rect.height + padding > window.innerHeight) {
                top = window.innerHeight - rect.height - padding;
            }
            if (left + rect.width + padding > window.innerWidth) {
                left = window.innerWidth - rect.width - padding;
            }
            menu.style.top = `${Math.max(padding, top)}px`;
            menu.style.left = `${Math.max(padding, left)}px`;
            menu.style.visibility = 'visible';
        });
    }, [hideContextMenu]);
    const onPaneClick = useCallback(() => {
        setSelectedNode(null);
        selectedNodeRef.current = null;
        hideContextMenu();
    }, [hideContextMenu]);
    useEffect(() => {
        syncCanvasState(nodes);
        window.nodes = nodes;
        syncSelectedNodeReference(nodes);
    }, [nodes, syncCanvasState]);
    useEffect(() => {
        selectedNodeRef.current = selectedNode;
    }, [selectedNode]);
    useEffect(() => {
        renderSelectedNodeSummary(selectedNode);
    }, [selectedNode]);
    useEffect(() => {
        window.edges = edges;
    }, [edges]);
    useEffect(() => {
        window.addNodeToFlow = (componentType, componentName, position) => {
            addNodeToCanvas(componentType, componentName, position);
        };
        return () => {
            if (window.addNodeToFlow) {
                delete window.addNodeToFlow;
            }
        };
    }, [addNodeToCanvas]);
    useEffect(() => {
        window.flowBuilderActions = {
            configure: configureNodeById,
            duplicate: duplicateNodeById,
            delete: deleteNodeById
        };
        return () => {
            if (window.flowBuilderActions) {
                delete window.flowBuilderActions;
            }
        };
    }, [configureNodeById, duplicateNodeById, deleteNodeById]);
    useEffect(() => {
        // Load existing flow if editing
        <?php if (isset($flow) && $flow && !empty($flow['flow_data'])): ?>
            <?php
            $flowData = $flow['flow_data'];
            // If flow_data is a string (JSON), decode it
            if (is_string($flowData)) {
                $decoded = json_decode($flowData, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $flowData = $decoded;
                }
            }
            ?>
            loadExistingFlow(<?php echo json_encode($flowData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>);
        <?php endif; ?>
    }, []);
    useEffect(() => {
        const menu = document.getElementById('node-context-menu');
        if (!menu) {
            return;
        }
        contextMenuRef.current = menu;
        const handleMenuClick = (event) => {
            event.preventDefault();
            event.stopPropagation();
            const button = event.target.closest('[data-action]');
            if (!button) {
                return;
            }
            handleContextAction(button.dataset.action);
        };
        menu.addEventListener('click', handleMenuClick);
        return () => {
            menu.removeEventListener('click', handleMenuClick);
        };
    }, [handleContextAction]);
    useEffect(() => {
        const handleGlobalClick = () => hideContextMenu();
        const handleEscape = (event) => {
            if (event.key === 'Escape') {
                hideContextMenu();
            }
        };
        const handleViewportChange = () => hideContextMenu();
        document.addEventListener('click', handleGlobalClick);
        document.addEventListener('keydown', handleEscape);
        window.addEventListener('scroll', handleViewportChange, true);
        window.addEventListener('resize', handleViewportChange);
        return () => {
            document.removeEventListener('click', handleGlobalClick);
            document.removeEventListener('keydown', handleEscape);
            window.removeEventListener('scroll', handleViewportChange, true);
            window.removeEventListener('resize', handleViewportChange);
        };
    }, [hideContextMenu]);
    if (!ReactFlowComponent) {
        return React.createElement('div', {
            className: 'alert alert-danger m-3'
        }, 'Unable to initialize the flow canvas. Please check that the React Flow library is available.');
    }
    return React.createElement('div', {
        ref: reactFlowWrapper,
        style: { width: '100%', height: '100%', position: 'relative' }
    },
        React.createElement(ReactFlowComponent, {
            nodes: nodes,
            edges: edges,
            onNodesChange: onNodesChange,
            onEdgesChange: onEdgesChange,
            onConnect: onConnect,
            onDrop: onDrop,
            onDragOver: onDragOver,
            onNodeClick: onNodeClick,
            onNodeDoubleClick: onNodeDoubleClick,
            onNodeContextMenu: onNodeContextMenu,
            onPaneClick: onPaneClick,
            onInit: onInit,
            nodeTypes: nodeTypes,
            fitView: true,
            attributionPosition: 'bottom-left',
            style: { background: 'transparent' }
        },
        React.createElement(Controls),
        React.createElement(MiniMap),
        React.createElement(Background)
        )
    );
}
// Component items drag handlers
function initializeFlowBuilderUI() {
    enterFlowBuilderFullscreen();
    startFlowBuilderLayoutMaintenance();
    ensureFlowBuilderHeaderBindings();
    window.addEventListener('beforeunload', function() {
        exitFlowBuilderFullscreen();
    }, { once: true });
    renderSelectedNodeSummary(null);
    const detailsContainer = document.getElementById('flow-details-panel');
    if (detailsContainer && window.React && window.ReactDOM) {
        try {
            const detailsRoot = ReactDOM.createRoot(detailsContainer);
            detailsRoot.render(React.createElement(FlowDetailsPanel, {
                text: FLOW_DETAILS_TEXT
            }));
        } catch (error) {
            console.error('Unable to render flow details panel:', error);
        }
    }

    let isDragging = false;
    // Make component items draggable
    document.querySelectorAll('.component-item').forEach(item => {
        item.setAttribute('role', 'button');
        item.setAttribute('tabindex', '0');
        item.addEventListener('dragstart', function(e) {
            isDragging = true;
            const componentType = this.dataset.type || 'node';
            const componentName = this.querySelector('.component-name') ? this.querySelector('.component-name').textContent : componentType;
            e.dataTransfer.setData('application/reactflow/type', componentType);
            e.dataTransfer.setData('application/reactflow/name', componentName);
            e.dataTransfer.setData('text/plain', componentType);
            e.dataTransfer.effectAllowed = 'copyMove';
            // Add visual feedback
            this.style.opacity = '0.5';
            this.style.transform = 'rotate(2deg)';
        });
        item.addEventListener('dragend', function(e) {
            isDragging = false;
            // Reset visual feedback
            this.style.opacity = '1';
            this.style.transform = 'rotate(0deg)';
        });
        item.addEventListener('click', function() {
            if (isDragging) {
                return;
            }
            const addNode = window.addNodeToFlow;
            if (typeof addNode === 'function') {
                const componentType = this.dataset.type;
                const componentName = this.querySelector('.component-name').textContent;
                addNode(componentType, componentName);
            }
        });
        item.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
    // Initialize React Flow
    const container = document.querySelector('.canvas-container');
    if (container) {
        const root = ReactDOM.createRoot(container);
        root.render(React.createElement(FlowBuilderCanvas));
    }
    // Add visual feedback for canvas
    const canvas = document.getElementById('flow-canvas');
    if (canvas) {
        canvas.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (e.dataTransfer) {
                e.dataTransfer.dropEffect = 'move';
            }
            this.classList.add('is-drop-target');
        });
        canvas.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('is-drop-target');
        });
        canvas.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('is-drop-target');
        });
    }
    const searchInput = document.getElementById('component-search');
    if (searchInput) {
        const filterComponents = (query) => {
            const normalizedQuery = query.trim().toLowerCase();
            document.querySelectorAll('.component-item').forEach(item => {
                const name = item.querySelector('.component-name').textContent.toLowerCase();
                const descriptionElement = item.querySelector('.component-description');
                const description = descriptionElement ? descriptionElement.textContent.toLowerCase() : '';
                const matches = !normalizedQuery || name.includes(normalizedQuery) || description.includes(normalizedQuery);
                item.style.display = matches ? 'flex' : 'none';
            });
        };
        searchInput.addEventListener('input', function() {
            filterComponents(this.value);
        });
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.value = '';
                filterComponents('');
            }
        });
    }
    // Add event listeners for floating controls
    document.querySelectorAll('.floating-control').forEach(button => {
        button.addEventListener('click', function() {
            const title = this.title;
            if (title === 'Zoom in') {
                zoomIn();
            } else if (title === 'Zoom out') {
                zoomOut();
            } else if (title === 'Fit to screen') {
                fitToScreen();
            } else if (title === 'Flow home') {
                window.location.href = FLOW_BUILDER_HOME_URL;
            }
        });
    });
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFlowBuilderUI);
} else {
    initializeFlowBuilderUI();
}
// Flow management functions
function saveFlow() {
    console.log('Saving flow...');

    // Ensure we have the latest nodes and edges
    const currentNodes = window.nodes || [];
    const currentEdges = window.edges || [];

    console.log('Current nodes:', currentNodes.length, currentNodes);
    console.log('Current edges:', currentEdges.length, currentEdges);

    // Deep clone nodes and edges to ensure all properties are preserved
    const flowData = {
        nodes: currentNodes.map(node => {
            const clonedNode = JSON.parse(JSON.stringify(node));
            // Ensure all required React Flow properties are present
            if (!clonedNode.id) {
                clonedNode.id = `${node.type || 'node'}_${Date.now()}_${Math.random()}`;
            }
            if (!clonedNode.position || typeof clonedNode.position.x !== 'number' || typeof clonedNode.position.y !== 'number') {
                clonedNode.position = { x: 100, y: 100 };
            }
            return clonedNode;
        }),
        edges: currentEdges.map(edge => {
            const clonedEdge = JSON.parse(JSON.stringify(edge));
            // Ensure all required React Flow properties are present
            if (!clonedEdge.id) {
                clonedEdge.id = `edge-${edge.source}-${edge.target}`;
            }
            if (!clonedEdge.type) {
                clonedEdge.type = 'smoothstep';
            }
            if (!clonedEdge.style) {
                clonedEdge.style = { stroke: '#555' };
            }
            return clonedEdge;
        })
    };

    const nameInput = document.getElementById('flow-name');
    const descriptionInput = document.getElementById('flow-description');

    if (typeof window.getFlowDetails !== 'function' && (!nameInput)) {
        alert_float('danger', 'Flow form is not ready yet. Please refresh the page and try again.');
        return;
    }

    const details = typeof window.getFlowDetails === 'function'
        ? window.getFlowDetails()
        : {
            name: nameInput ? nameInput.value : '',
            description: descriptionInput ? descriptionInput.value : ''
        };

    const flowName = (details.name || '').trim();
    const flowDescription = (details.description || '').trim();

    if (!flowName) {
        alert_float('danger', 'Flow name is required before saving.');
        if (nameInput) {
            nameInput.focus();
        }
        return;
    }

    flowDetailsStore.set({
        name: flowName,
        description: flowDescription
    });

    const payload = {
        name: flowName,
        description: flowDescription,
        flow_data: flowData,
        status: 1
    };

    if (EXISTING_FLOW_ID) {
        payload.id = EXISTING_FLOW_ID;
    }

    console.log('Saving payload:', payload);

    const saveButton = document.querySelector('button[onclick="saveFlow()"]');
    if (saveButton) {
        saveButton.disabled = true;
        saveButton.classList.add('disabled');
    }

    $.ajax({
        url: admin_url + 'flow_builder/save_flow',
        type: 'POST',
        data: payload,
        success: function(response) {
            console.log('Save response:', response);
            if (response && response.success) {
                alert_float('success', '<?php echo _l('flow_saved_successfully'); ?>');
                if (!EXISTING_FLOW_ID && response.flow_id) {
                    setTimeout(function() {
                        window.location.href = admin_url + 'flow_builder/build/' + response.flow_id;
                    }, 500);
                }
            } else {
                const message = response && response.message ? response.message : 'Error saving flow';
                alert_float('danger', message);
                console.error('Save error:', response);
            }
        },
        error: function(xhr, status, error) {
            alert_float('danger', 'Error saving flow');
            console.error('Save error:', xhr, status, error);
        },
        complete: function() {
            if (saveButton) {
                saveButton.disabled = false;
                saveButton.classList.remove('disabled');
            }
        }
    });
}
function testFlow() {
    if (!EXISTING_FLOW_ID) {
        alert_float('warning', 'Please save the flow first before testing');
        return;
    }

    $.ajax({
        url: admin_url + 'flow_builder/execute_flow/' + EXISTING_FLOW_ID,
        type: 'POST',
        success: function(response) {
            if (response && response.success) {
                const executionTime = response.execution_time ? Number(response.execution_time).toFixed(4) : '0.0000';
                const message = response.log_message || response.message || DEFAULT_SUCCESS_MESSAGE;
                alert_float('success', message + ' (Execution time: ' + executionTime + 's)');
            } else {
                const errorMessage = response && response.message ? response.message : DEFAULT_FAILURE_MESSAGE;
                if (response && response.error) {
                    console.error('Flow execution error context:', response.error);
                } else if (response && response.result) {
                    console.error('Flow execution error result:', response.result);
                }
                alert_float('danger', errorMessage);
            }
        },
        error: function() {
            alert_float('danger', 'Error executing flow');
        }
    });
}
function exportFlow() {
    const flowData = {
        nodes: window.nodes || [],
        edges: window.edges || []
    };
    const dataStr = JSON.stringify(flowData, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);
    const exportFileDefaultName = 'flow_export_' + new Date().toISOString().slice(0, 10) + '.json';
    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();
}
function showNodeConfiguration(node) {
    // Ensure the node is properly set globally
    window.selectedNode = node;
    const nodeType = node.data.type;
    let configHtml = '';
    switch (nodeType) {
        case 'api_trigger':
            configHtml = `
                <div class="api-trigger-config">
                    <div class="config-card accent-card">
                        <div class="config-card-heading">
                            <span class="config-card-icon">
                                <i class="fa fa-plug"></i>
                            </span>
                            <div>
                                <h6 class="config-card-title"><?php echo _l('flow_builder_api_trigger_heading'); ?></h6>
                                <p class="config-card-subtitle">Choose the external API that will kick off this flow.</p>
                            </div>
                        </div>
                        <div class="config-card-body">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold mb-1"><?php echo _l('flow_builder_select_external_api'); ?></label>
                                <select class="form-control form-control-sm" id="api-trigger-select">
                                    <option value=""><?php echo _l('flow_builder_select_external_api_placeholder'); ?></option>
                                </select>
                                <small class="text-muted d-block mt-2 api-trigger-preview" id="api-trigger-preview"><?php echo _l('flow_builder_api_preview'); ?></small>
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-bold mb-1"><?php echo _l('flow_builder_variable_name'); ?></label>
                                <input type="text" class="form-control form-control-sm" id="api-trigger-variable" placeholder="api_response">
                                <small class="text-muted d-block mt-1"><?php echo _l('flow_builder_variable_hint'); ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="config-card neutral-card">
                        <div class="config-card-heading">
                            <span class="config-card-icon">
                                <i class="fa fa-signal"></i>
                            </span>
                            <div>
                                <h6 class="config-card-title"><?php echo _l('flow_builder_test_api_connection'); ?></h6>
                                <p class="config-card-subtitle">Verify credentials and preview the live payload.</p>
                            </div>
                        </div>
                        <div class="config-card-body">
                            <div class="api-test-actions">
                                <button type="button" class="btn btn-primary btn-with-icon" id="test-api-btn" onclick="testApiConnection()">
                                    <i class="fa fa-play"></i><span><?php echo _l('flow_builder_test_api'); ?></span>
                                </button>
                                <p class="api-test-note mb-0">Runs a real request using this configuration so you can confirm the response shape.</p>
                            </div>
                            <div id="test-results" class="api-test-results" style="display: none;">
                                <div class="alert alert-info mb-2" id="test-status">
                                    <i class="fa fa-spinner fa-spin alert-leading-icon"></i><?php echo _l('flow_builder_testing_api'); ?>
                                </div>
                                <div id="test-response" class="d-flex align-items-center" style="display: none;">
                                    <button type="button" class="btn btn-outline-info btn-sm btn-with-icon" onclick="viewApiResponse()">
                                        <i class="fa fa-eye"></i><span><?php echo _l('flow_builder_view_response'); ?></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="config-card neutral-card span-2">
                        <div class="config-card-heading">
                            <span class="config-card-icon">
                                <i class="fa fa-life-ring"></i>
                            </span>
                            <div>
                                <h6 class="config-card-title">HTTP Status Handling</h6>
                                <p class="config-card-subtitle">Enable quick branching by marking the response codes you care about.</p>
                            </div>
                        </div>
                        <div class="config-card-body">
                            <div class="status-routing-toggle">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="http-status-routing-enabled">
                                    <label class="custom-control-label" for="http-status-routing-enabled">Enable status-based routing</label>
                                </div>
                                <small class="text-muted d-block mt-2">When enabled, the trigger exposes <code>{{http_status_code}}</code> and highlights the statuses you select below.</small>
                            </div>
                            <div class="status-routing-options mt-3" id="http-status-options">
                                <!-- Populated via JS -->
                            </div>
                            <div class="status-routing-summary mt-3">
                                <span class="status-summary-label">Active routes:</span>
                                <div id="http-status-summary" class="status-summary-chips"></div>
                            </div>
                            <small class="text-muted d-block mt-2">Tip: Add a Condition node and compare <code>{{http_status_route}}</code> or <code>{{api_response_status}}</code> to direct the flow.</small>
                            <div class="status-custom-entry">
                                <label>Add custom status code</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" min="100" max="599" class="form-control" id="http-status-custom-input" placeholder="e.g. 418">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary btn-with-icon" id="http-status-add-btn">
                                            <i class="fa fa-plus"></i><span>Add</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="config-card response-card span-2">
                        <div class="config-card-heading">
                            <span class="config-card-icon">
                                <i class="fa fa-code-fork"></i>
                            </span>
                            <div>
                                <h6 class="config-card-title">Response Variables</h6>
                                <p class="config-card-subtitle">Give important fields friendly names for downstream nodes. The raw payload is always available as <code>{{variable_name}}_raw</code>.</p>
                            </div>
                        </div>
                        <div class="config-card-body response-variable-manager-card">
                            <div id="response-variable-summary" class="response-variable-summary"></div>
                            <div class="response-variable-toolbar">
                                <p class="api-test-note mb-0">Perfect for building tickets, notifications, or conditional logic.</p>
                            </div>
                            <div id="response-variable-editor" class="response-variable-editor mt-1"></div>
                        </div>
                    </div>
                </div>
            `;
            break;
        case 'condition':
            configHtml = `
                <div class="condition-config">
                    <div class="config-card accent-card">
                        <div class="config-card-heading">
                            <span class="config-card-icon">
                                <i class="fa fa-filter"></i>
                            </span>
                            <div>
                                <h6 class="config-card-title">Choose a field to evaluate</h6>
                                <p class="config-card-subtitle">Use dot notation (<code>data.status</code>) or pick a generated variable.</p>
                            </div>
                        </div>
                        <div class="config-card-body">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold mb-1">Response Field</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="condition-field" placeholder="e.g., data.status or {{api_response.status}}" list="condition-field-options">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary btn-with-icon" type="button" onclick="showVariablePicker('condition-field')">
                                            <i class="fa fa-list-ul"></i><span>Variables</span>
                                        </button>
                                    </div>
                                </div>
                                <datalist id="condition-field-options"></datalist>
                                <small class="text-muted d-block mt-2">Field path or variable reference ({{variable_name.field}}). Recent API tests will populate suggestions.</small>
                            </div>
                        </div>
                    </div>
                    <div class="config-card neutral-card">
                        <div class="config-card-heading">
                            <span class="config-card-icon">
                                <i class="fa fa-balance-scale"></i>
                            </span>
                            <div>
                                <h6 class="config-card-title">Comparison Operator</h6>
                                <p class="config-card-subtitle">Tell the condition how to evaluate the field.</p>
                            </div>
                        </div>
                        <div class="config-card-body">
                            <div class="operator-pill-group" id="condition-operator-pills">
                                <button type="button" class="operator-pill" data-operator="equals">Equals</button>
                                <button type="button" class="operator-pill" data-operator="not_equals">Not Equals</button>
                                <button type="button" class="operator-pill" data-operator="contains">Contains</button>
                                <button type="button" class="operator-pill" data-operator="greater_than">Greater Than</button>
                                <button type="button" class="operator-pill" data-operator="less_than">Less Than</button>
                            </div>
                            <select class="form-control form-control-sm mt-3" id="condition-operator">
                                <option value="equals"><?php echo _l('equals'); ?></option>
                                <option value="not_equals"><?php echo _l('not_equals'); ?></option>
                                <option value="contains"><?php echo _l('contains'); ?></option>
                                <option value="greater_than"><?php echo _l('greater_than'); ?></option>
                                <option value="less_than"><?php echo _l('less_than'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="config-card response-card">
                        <div class="config-card-heading">
                            <span class="config-card-icon">
                                <i class="fa fa-bullseye"></i>
                            </span>
                            <div>
                                <h6 class="config-card-title">Expected Value</h6>
                                <p class="config-card-subtitle">Match against a literal value or another variable.</p>
                            </div>
                        </div>
                        <div class="config-card-body">
                            <div class="form-group mb-0">
                                <label class="font-weight-bold mb-1">Compare To</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" id="condition-value" placeholder="e.g., active or {{api_response.expected_status}}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary btn-with-icon" type="button" onclick="showVariablePicker('condition-value')">
                                            <i class="fa fa-list-ul"></i><span>Variables</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="condition-quick-values mt-2" id="condition-quick-values">
                                    <span class="quick-value-label">Quick insert:</span>
                                    <button type="button" class="quick-value-chip" data-value="{{http_status_code}}">HTTP Status</button>
                                    <button type="button" class="quick-value-chip" data-value="{{http_status_route}}">Status Route</button>
                                    <button type="button" class="quick-value-chip" data-value="true">true</button>
                                    <button type="button" class="quick-value-chip" data-value="false">false</button>
                                    <button type="button" class="quick-value-chip" data-value="null">null</button>
                                </div>
                                <small class="text-muted d-block mt-2">Use static values (text, numbers) or any available variable ({{variable_name}}).</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            break;
        case 'staff_create':
        case 'staff_update':
            configHtml = `
                <div class="config-section">
                    <h6>Staff Configuration</h6>
                    ${nodeType === 'staff_update' ? `<div class="form-group">
                        <label>Staff ID Field</label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm" id="staff-id-field" placeholder="e.g., data.staff_id or {{api_response.staff_id}}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary btn-sm" type="button" onclick="showVariablePicker('staff-id-field')">Vars</button>
                            </div>
                        </div>
                        <small class="text-muted">Field path or variable reference ({{variable_name.field}})</small>
                    </div>` : ''}
                    <div class="form-group">
                        <label>Field Mapping</label>
                        <div id="field-mapping-container">
                            <div class="mapping-row">
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm mapping-source" placeholder="API field or {{variable.field}}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="showVariablePickerForMapping(this)">Vars</button>
                                    </div>
                                </div>
                                <span class="mx-2">→</span>
                                <select class="form-control form-control-sm mapping-target">
                                    <option value="">Select staff field...</option>
                                </select>
                                <button class="btn btn-sm btn-danger ml-2" onclick="removeMapping(this)">×</button>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-primary mt-2" onclick="addMapping()">Add Mapping</button>
                        <small class="text-muted mt-1 d-block">Use field paths (data.field) or variable references ({{variable_name.field}})</small>
                    </div>
                </div>
            `;
            break;
        case 'ticket_create':
        case 'ticket_update':
            configHtml = `
                <div class="config-section">
                    <h6>Ticket Configuration</h6>
                    ${nodeType === 'ticket_update' ? '<div class="form-group"><label>Ticket ID Field</label><input type="text" class="form-control form-control-sm" id="ticket-id-field" placeholder="e.g., data.ticket_id"></div>' : ''}
                    <div class="form-group">
                        <label>Field Mapping</label>
                        <div id="field-mapping-container">
                            <div class="mapping-row">
                                <select class="form-control form-control-sm mapping-source">
                                    <option value="">Select API field...</option>
                                </select>
                                <span class="mx-2">→</span>
                                <select class="form-control form-control-sm mapping-target">
                                    <option value="">Select ticket field...</option>
                                </select>
                                <button class="btn btn-sm btn-danger ml-2" onclick="removeMapping(this)">×</button>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-primary mt-2" onclick="addMapping()">Add Mapping</button>
                    </div>
                </div>
            `;
            break;
        case 'sms_send':
            configHtml = `
                <div class="config-section">
                    <h6>SMS Configuration</h6>
                    <div class="form-group">
                        <label>Phone Number Field</label>
                        <input type="text" class="form-control form-control-sm" id="sms-phone-field" placeholder="e.g., data.phone">
                    </div>
                    <div class="form-group">
                        <label>Message Field</label>
                        <input type="text" class="form-control form-control-sm" id="sms-message-field" placeholder="e.g., data.message">
                    </div>
                </div>
            `;
            break;
        case 'whatsapp_send':
            configHtml = `
                <div class="config-section">
                    <h6>WhatsApp Configuration</h6>
                    <div class="form-group">
                        <label>Phone Number Field</label>
                        <input type="text" class="form-control form-control-sm" id="whatsapp-phone-field" placeholder="e.g., data.phone">
                    </div>
                    <div class="form-group">
                        <label>Message Field</label>
                        <input type="text" class="form-control form-control-sm" id="whatsapp-message-field" placeholder="e.g., data.message">
                    </div>
                </div>
            `;
            break;
        case 'email_send':
            configHtml = `
                <div class="config-section">
                    <h6>Email Configuration</h6>
                    <div class="form-group">
                        <label>Email Field</label>
                        <input type="text" class="form-control form-control-sm" id="email-address-field" placeholder="e.g., data.email">
                    </div>
                    <div class="form-group">
                        <label>Subject Field</label>
                        <input type="text" class="form-control form-control-sm" id="email-subject-field" placeholder="e.g., data.subject">
                    </div>
                    <div class="form-group">
                        <label>Body Field</label>
                        <input type="text" class="form-control form-control-sm" id="email-body-field" placeholder="e.g., data.body">
                    </div>
                </div>
            `;
            break;
        default:
            configHtml = '<p>No configuration needed for this node type.</p>';
    }
    document.getElementById('nodeConfigBody').innerHTML = `
        <div class="node-config-content">
            ${configHtml}
        </div>
    `;
    document.getElementById('nodeConfigTitle').textContent = node.data.label + ' Configuration';
    $('#nodeConfigModal').modal('show');
    // Load field options based on node type
    if (nodeType === 'api_trigger') {
        setTimeout(function() {
            updateResponseVariableSummary(node);
            openResponseVariablesModal();
            initializeHttpStatusRouting(node);
        }, 0);
        loadExternalApis(node);
    } else if (nodeType === 'condition') {
        initializeConditionConfig(node);
        loadFieldOptions(nodeType, getActiveApiId());
    } else {
        loadFieldOptions(nodeType, getActiveApiId());
    }
}
function loadExternalApis(node) {
    const select = document.getElementById('api-trigger-select');
    const preview = document.getElementById('api-trigger-preview');
    const variableInput = document.getElementById('api-trigger-variable');
    if (variableInput) {
        const existingVariable = node && node.data ? (node.data.variable_name || (node.data.config && node.data.config.variable_name)) : '';
        variableInput.value = existingVariable || 'api_response';
    }
    if (!select) {
        return;
    }
    const currentId = node && node.data ? (node.data.external_api_id || (node.data.config && node.data.config.external_api_id)) : '';
    const setPreview = () => {
        if (!preview) {
            return;
        }
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption && selectedOption.value) {
            const method = selectedOption.dataset.method || '';
            const url = selectedOption.dataset.url || '';
            preview.innerHTML = `<span class="d-block font-weight-bold">${selectedOption.textContent}</span><small class="text-muted">${method} ${url}</small>`;
        } else {
            preview.textContent = `<?php echo _l('flow_builder_api_preview'); ?>`;
        }
    };
    select.innerHTML = `<option value=""><?php echo _l('flow_builder_loading_external_apis'); ?></option>`;
    select.disabled = true;
    if (preview) {
        preview.textContent = `<?php echo _l('flow_builder_api_preview'); ?>`;
    }
    $.getJSON(admin_url + 'external_api_settings/get_external_apis', function(response) {
        select.innerHTML = `<option value=""><?php echo _l('flow_builder_select_external_api_placeholder'); ?></option>`;
        if (!response || !response.length) {
            select.innerHTML = `<option value=""><?php echo _l('flow_builder_no_external_apis'); ?></option>`;
            select.disabled = true;
            if (preview) {
                preview.textContent = `<?php echo _l('flow_builder_no_external_apis'); ?>`;
            }
            return;
        }
        select.disabled = false;
        response.forEach(function(api) {
            const option = document.createElement('option');
            option.value = api.id;
            const method = (api.request_method || 'GET').toUpperCase();
            option.textContent = method + ' - ' + api.name;
            option.dataset.method = method;
            option.dataset.url = api.api_url || '';
            select.appendChild(option);
        });
        if (currentId) {
            select.value = currentId;
        }
        setPreview();
    }).fail(function() {
        select.innerHTML = `<option value=""><?php echo _l('flow_builder_unable_to_load_external_apis'); ?></option>`;
        select.disabled = true;
        if (preview) {
            preview.textContent = `<?php echo _l('flow_builder_unable_to_load_external_apis'); ?>`;
        }
    });
    select.onchange = setPreview;
}
function getActiveApiId() {
    const nodes = window.nodes || [];
    const triggerNode = nodes.find(n => n.data && n.data.type === 'api_trigger' && (n.data.external_api_id || (n.data.config && n.data.config.external_api_id)));
    if (!triggerNode) {
        return null;
    }
    return triggerNode.data.external_api_id || (triggerNode.data.config ? triggerNode.data.config.external_api_id : null);
}
function loadFieldOptions(nodeType, externalApiId) {
    if (typeof externalApiId === 'undefined') {
        externalApiId = getActiveApiId();
    }
    // Load API response fields
    const requestData = externalApiId ? { api_id: externalApiId } : {};
    $.getJSON(admin_url + 'flow_builder/get_api_response_fields', requestData, function(apiFields) {
        if (!apiFields) {
            apiFields = {};
        }
        // Populate API field selectors
        document.querySelectorAll('[id$="-field"], .mapping-source').forEach(select => {
            if (select.tagName === 'SELECT') {
                select.innerHTML = '<option value="">Select API field...</option>';
                Object.keys(apiFields).forEach(key => {
                    const option = document.createElement('option');
                    option.value = key;
                    option.textContent = apiFields[key] + ' (' + key + ')';
                    select.appendChild(option);
                });
            }
        });
    });
    // Load target fields based on node type
    // Load target fields based on node type
    let targetFields = {};
    switch (nodeType) {
        case 'staff_create':
        case 'staff_update':
            $.get(admin_url + 'flow_builder/get_staff_fields', function(fields) {
                targetFields = JSON.parse(fields);
                populateTargetFields(targetFields);
            });
            break;
        case 'ticket_create':
        case 'ticket_update':
            $.get(admin_url + 'flow_builder/get_ticket_fields', function(fields) {
                targetFields = JSON.parse(fields);
                populateTargetFields(targetFields);
            });
            break;
        case 'division_create':
        case 'division_update':
            $.get(admin_url + 'flow_builder/get_division_fields', function(fields) {
                targetFields = JSON.parse(fields);
                populateTargetFields(targetFields);
            });
            break;
        case 'department_create':
        case 'department_update':
            $.get(admin_url + 'flow_builder/get_department_fields', function(fields) {
                targetFields = JSON.parse(fields);
                populateTargetFields(targetFields);
            });
            break;
    }
}
function populateTargetFields(fields) {
    document.querySelectorAll('.mapping-target').forEach(select => {
        select.innerHTML = '<option value="">Select target field...</option>';
        Object.keys(fields).forEach(key => {
            const option = document.createElement('option');
            option.value = key;
            option.textContent = fields[key] + ' (' + key + ')';
            select.appendChild(option);
        });
    });
}
function addMapping() {
    const container = document.getElementById('field-mapping-container');
    const mappingRow = document.createElement('div');
    mappingRow.className = 'mapping-row';
    mappingRow.innerHTML = `
        <select class="form-control form-control-sm mapping-source">
            <option value="">Select API field...</option>
        </select>
        <span class="mx-2">→</span>
        <select class="form-control form-control-sm mapping-target">
            <option value="">Select target field...</option>
        </select>
        <button class="btn btn-sm btn-danger ml-2" onclick="removeMapping(this)">×</button>
    `;
    container.appendChild(mappingRow);
}
function removeMapping(button) {
    button.closest('.mapping-row').remove();
}
function saveNodeConfiguration() {
    if (!window.selectedNode) {
        console.error('No selected node found');
        alert_float('warning', 'No node selected for configuration');
        return;
    }
    const node = window.selectedNode;
    const nodeType = node.data.type;
    let config = {};
    switch (nodeType) {
        case 'api_trigger':
            const apiSelect = document.getElementById('api-trigger-select');
            if (!apiSelect || !apiSelect.value) {
                alert_float('warning', '<?php echo _l('flow_builder_choose_external_api'); ?>');
                return;
            }
            const selectedOption = apiSelect.options[apiSelect.selectedIndex];
            const rawVariableNameInput = document.getElementById('api-trigger-variable');
            const existingGeneratedVariables = Array.isArray(node.data.config && node.data.config.generated_variables)
                ? node.data.config.generated_variables
                : [];
            const sanitizedVariable = sanitizeVariableName(rawVariableNameInput ? rawVariableNameInput.value : 'api_response');
            const normalizedGeneratedVariables = normalizeGeneratedVariablePaths(existingGeneratedVariables, sanitizedVariable);
            const statusToggle = document.getElementById('http-status-routing-enabled');
            const statusContainer = document.getElementById('http-status-options');
            let httpStatusRouting = { enabled: false, statuses: [] };
            if (statusToggle && statusContainer) {
                httpStatusRouting.enabled = statusToggle.checked;
                if (httpStatusRouting.enabled) {
                    httpStatusRouting.statuses = Array.from(statusContainer.querySelectorAll('.http-status-option:checked'))
                        .map(cb => parseInt(cb.value, 10))
                        .filter(code => !Number.isNaN(code));
                }
            }
            config = {
                external_api_id: parseInt(apiSelect.value, 10),
                variable_name: sanitizedVariable,
                external_api_label: selectedOption ? selectedOption.textContent : '',
                api_method: selectedOption ? selectedOption.dataset.method : '',
                api_url: selectedOption ? selectedOption.dataset.url : '',
                generated_variables: normalizedGeneratedVariables,
                http_status_routing: httpStatusRouting
            };
            node.data.variable_name = sanitizedVariable;
            node.data.external_api_id = config.external_api_id;
            node.data.external_api_label = config.external_api_label;
            node.data.api_method = config.api_method;
            node.data.api_url = config.api_url;
            node.data.subtitle = config.external_api_label || node.data.subtitle;
            node.data.config = config;
            node.data.http_status_routing = httpStatusRouting;
            updateResponseVariableSummary(node);
            // Initialize variable storage for this API trigger
            if (!window.flowVariables) {
                window.flowVariables = {};
            }
            if (!Object.prototype.hasOwnProperty.call(window.flowVariables, sanitizedVariable)) {
                window.flowVariables[sanitizedVariable] = null; // Will be populated when API is executed
            }
            const rawVariableKey = sanitizedVariable + '_raw';
            if (!Object.prototype.hasOwnProperty.call(window.flowVariables, rawVariableKey)) {
                window.flowVariables[rawVariableKey] = null;
            }
            break;
        case 'condition':
            config = {
                field: document.getElementById('condition-field').value,
                operator: document.getElementById('condition-operator').value,
                value: document.getElementById('condition-value').value
            };
            break;
        case 'staff_create':
        case 'staff_update':
            config = {
                <?php if (isset($flow_id) && $nodeType === 'staff_update'): ?>
                staff_id_field: document.getElementById('staff-id-field').value,
                <?php endif; ?>
                mapping: []
            };
            document.querySelectorAll('.mapping-row').forEach(row => {
                const sourceSelect = row.querySelector('.mapping-source');
                const targetSelect = row.querySelector('.mapping-target');
                if (sourceSelect.value && targetSelect.value) {
                    config.mapping.push({
                        source_field: sourceSelect.value,
                        target_field: targetSelect.value,
                        value_type: 'field'
                    });
                }
            });
            break;
        case 'ticket_create':
        case 'ticket_update':
            config = {
                <?php if (isset($flow_id) && $nodeType === 'ticket_update'): ?>
                ticket_id_field: document.getElementById('ticket-id-field').value,
                <?php endif; ?>
                mapping: []
            };
            document.querySelectorAll('.mapping-row').forEach(row => {
                const sourceSelect = row.querySelector('.mapping-source');
                const targetSelect = row.querySelector('.mapping-target');
                if (sourceSelect.value && targetSelect.value) {
                    config.mapping.push({
                        source_field: sourceSelect.value,
                        target_field: targetSelect.value,
                        value_type: 'field'
                    });
                }
            });
            break;
        case 'sms_send':
            config = {
                phone_field: document.getElementById('sms-phone-field').value,
                message_field: document.getElementById('sms-message-field').value
            };
            break;
        case 'whatsapp_send':
            config = {
                phone_field: document.getElementById('whatsapp-phone-field').value,
                message_field: document.getElementById('whatsapp-message-field').value
            };
            break;
        case 'email_send':
            config = {
                email_field: document.getElementById('email-address-field').value,
                subject_field: document.getElementById('email-subject-field').value,
                body_field: document.getElementById('email-body-field').value
            };
            break;
    }
    // Update node data
    node.data.config = config;
    // Update the nodes in React Flow
    const applyConfigChanges = (nodeList) => nodeList.map(n => {
        if (n.id !== node.id) {
            return n;
        }
        return {
            ...n,
            data: {
                ...n.data,
                ...node.data,
                config
            }
        };
    });

    if (typeof window.setNodes === 'function') {
        window.setNodes(nodes => {
            const updatedNodes = applyConfigChanges(nodes);
            window.nodes = updatedNodes;
            syncSelectedNodeReference(updatedNodes);
            return updatedNodes;
        });
        // Force refresh the node display to show configuration status
        setTimeout(() => {
            if (typeof window.setNodes === 'function') {
                window.setNodes(nodes => {
                    const refreshedNodes = [...nodes];
                    window.nodes = refreshedNodes;
                    syncSelectedNodeReference(refreshedNodes);
                    return refreshedNodes;
                });
            } else if (Array.isArray(window.nodes)) {
                window.nodes = [...window.nodes];
                syncSelectedNodeReference(window.nodes);
            }
        }, 50);
    } else if (Array.isArray(window.nodes)) {
        window.nodes = applyConfigChanges(window.nodes);
        syncSelectedNodeReference(window.nodes);
    }
    alert_float('success', 'Configuration saved successfully!');
    renderSelectedNodeSummary(window.selectedNode || null);
    $('#nodeConfigModal').modal('hide');
}
function loadExistingFlow(flowData) {
    if (flowData && flowData.nodes) {
        console.log('Loading existing flow with', flowData.nodes.length, 'nodes and', (flowData.edges || []).length, 'edges');

        // Ensure nodes have proper structure and preserve all properties
        const enhancedNodes = (flowData.nodes || []).map(node => {
            const enhanced = ensureBuilderNode(node);
            console.log('Enhanced node:', enhanced.id, enhanced);
            return enhanced;
        });

        // Ensure edges have proper structure
        const enhancedEdges = (flowData.edges || []).map(edge => ({
            ...edge,
            id: edge.id || `edge-${edge.source}-${edge.target}`,
            type: edge.type || 'smoothstep',
            style: edge.style || { stroke: '#555' }
        }));

        seedFlowVariables(enhancedNodes);

        if (typeof window.setNodes === 'function' && typeof window.setEdges === 'function') {
            console.log('Setting nodes and edges via React Flow API');
            window.setNodes(enhancedNodes);
            window.setEdges(enhancedEdges);

            // Force a refresh after a short delay to ensure proper rendering
            setTimeout(() => {
                if (typeof window.getReactFlowInstance === 'function') {
                    const instance = window.getReactFlowInstance();
                    if (instance && typeof instance.fitView === 'function') {
                        instance.fitView({ padding: 0.1 });
                    }
                }
            }, 100);
        } else {
            console.log('React Flow API not available, storing pending data');
            window.__pendingFlowData = {
                nodes: enhancedNodes,
                edges: enhancedEdges
            };
        }

        // Update canvas state
        const canvasEl = document.getElementById('flow-canvas');
        if (canvasEl) {
            canvasEl.dataset.hasNodes = enhancedNodes.length > 0 ? 'true' : 'false';
        }
    } else {
        console.log('No flow data to load or missing nodes');
    }
}

function seedFlowVariables(nodes) {
    if (!Array.isArray(nodes) || nodes.length === 0) {
        return;
    }
    if (!window.flowVariables) {
        window.flowVariables = {};
    }
    nodes.forEach(node => {
        if (!node || !node.data) {
            return;
        }
        const data = node.data;
        const config = data.config || {};
        const baseVar = data.variable_name || config.variable_name;
        if (baseVar && !Object.prototype.hasOwnProperty.call(window.flowVariables, baseVar)) {
            window.flowVariables[baseVar] = null;
        }
        if (baseVar) {
            const rawKey = baseVar + '_raw';
            if (!Object.prototype.hasOwnProperty.call(window.flowVariables, rawKey)) {
                window.flowVariables[rawKey] = null;
            }
            const statusKey = baseVar + '_status';
            if (!Object.prototype.hasOwnProperty.call(window.flowVariables, statusKey)) {
                window.flowVariables[statusKey] = null;
            }
        }
        if (Array.isArray(config.generated_variables)) {
            config.generated_variables.forEach(definition => {
                if (!definition || !definition.name) {
                    return;
                }
                if (!Object.prototype.hasOwnProperty.call(window.flowVariables, definition.name)) {
                    window.flowVariables[definition.name] = definition.sample_value ?? null;
                }
            });
        }
    });
    if (!Object.prototype.hasOwnProperty.call(window.flowVariables, 'http_status_code')) {
        window.flowVariables.http_status_code = null;
    }
    if (!Object.prototype.hasOwnProperty.call(window.flowVariables, 'http_status_route')) {
        window.flowVariables.http_status_route = null;
    }
}
// Canvas control functions
function zoomIn() {
    if (reactFlowInstance) {
        reactFlowInstance.zoomIn();
    }
}
function zoomOut() {
    if (reactFlowInstance) {
        reactFlowInstance.zoomOut();
    }
}
function fitToScreen() {
    if (reactFlowInstance) {
        reactFlowInstance.fitView();
    }
}
// Make functions globally available
window.saveFlow = saveFlow;
window.testFlow = testFlow;
window.exportFlow = exportFlow;
window.showNodeConfiguration = showNodeConfiguration;
window.saveNodeConfiguration = saveNodeConfiguration;
window.loadFieldOptions = loadFieldOptions;
window.addMapping = addMapping;
window.removeMapping = removeMapping;
window.zoomIn = zoomIn;
window.zoomOut = zoomOut;
window.fitToScreen = fitToScreen;
window.navigateBackToFlowList = navigateBackToFlowList;
window.startFlowBuilderLayoutMaintenance = startFlowBuilderLayoutMaintenance;
window.stopFlowBuilderLayoutMaintenance = stopFlowBuilderLayoutMaintenance;
// Variable management functions
const RESPONSE_VARIABLE_SOURCE_OPTIONS = [
    { value: 'field', label: 'Custom Attribute' },
    { value: 'entire', label: 'Entire Response Body' }
];
const ENTIRE_RESPONSE_LABEL = 'Entire response body';
let selectedVariableTarget = null;
let currentApiResponse = null; // Store current API response for viewing
let responseVariableEditor = { baseVariable: '', rows: [] };
let availableResponseFieldMap = {};
let responseVariableIdCounter = 0;
const HTTP_STATUS_OPTIONS = [
    { code: 200, label: '200 OK' },
    { code: 201, label: '201 Created' },
    { code: 204, label: '204 No Content' },
    { code: 400, label: '400 Bad Request' },
    { code: 401, label: '401 Unauthorized' },
    { code: 403, label: '403 Forbidden' },
    { code: 404, label: '404 Not Found' },
    { code: 422, label: '422 Unprocessable Entity' },
    { code: 500, label: '500 Server Error' },
    { code: 503, label: '503 Service Unavailable' }
];

// API Connection Testing and Response Viewing
function testApiConnection() {
    const apiSelect = document.getElementById('api-trigger-select');
    const testBtn = document.getElementById('test-api-btn');
    const testResults = document.getElementById('test-results');
    const testStatus = document.getElementById('test-status');
    const testResponse = document.getElementById('test-response');

    if (!apiSelect || !apiSelect.value) {
        alert_float('warning', 'Please select an external API first.');
        return;
    }

    // Show testing state
    testResults.style.display = 'block';
    testStatus.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Testing API connection...';
    testResponse.style.display = 'none';
    testBtn.disabled = true;

    // Make API test call
    $.ajax({
        url: admin_url + 'flow_builder/test_api_connection',
        type: 'POST',
        data: {
            external_api_id: apiSelect.value
        },
        success: function(response) {
            console.log('API Test Response:', response); // Debug logging

            // Make sure we are working with a parsed JSON object
            let parsedResponse = response;
            if (typeof response === 'string') {
                try {
                    parsedResponse = JSON.parse(response);
                } catch (err) {
                    console.error('Failed to parse API test response', err);
                    testStatus.innerHTML = '<i class="fa fa-exclamation-triangle text-danger"></i> <strong>API Test Failed</strong><br>' +
                        'Error: Received invalid JSON response from the server.';
                    testResponse.style.display = 'none';
                    $('#test-status').removeClass('alert-info alert-success').addClass('alert-danger');
                    return;
                }
            }

            if (parsedResponse && parsedResponse.success === true) {
                // Store response data
                currentApiResponse = {
                    response_structure: parsedResponse.response_structure,
                    raw_response: parsedResponse.raw_response,
                    decoded_response: parsedResponse.decoded_response,
                    http_code: parsedResponse.http_code,
                    response_type: parsedResponse.response_type
                };

                const variableInput = document.getElementById('api-trigger-variable');
                const baseVariable = sanitizeVariableName(variableInput ? variableInput.value : 'api_response');
                updateVariableStorage(parsedResponse.decoded_response, baseVariable);
                refreshDerivedVariables(baseVariable, parsedResponse.decoded_response);
                renderResponseVariablesList();
                initializeHttpStatusRouting(window.selectedNode, { preserveExisting: true });

                testStatus.innerHTML = '<i class="fa fa-check-circle text-success"></i> <strong>API Test Successful!</strong><br>' +
                    'HTTP Status: ' + parsedResponse.http_code + '<br>' +
                    'Response Type: ' + (parsedResponse.response_type || 'object') + '<br>' +
                    'Fields Found: ' + (parsedResponse.response_structure ? parsedResponse.response_structure.length : 0);
                testResponse.style.display = 'block';
                $('#test-status').removeClass('alert-info alert-danger').addClass('alert-success');
            } else {
                let errorMessage = 'Unknown error';
                if (parsedResponse && parsedResponse.message) {
                    errorMessage = parsedResponse.message;
                } else if (parsedResponse && parsedResponse.error) {
                    errorMessage = parsedResponse.error;
                }

                let errorHtml = '<i class="fa fa-exclamation-triangle text-danger"></i> <strong>API Test Failed</strong><br>' +
                    'Error: ' + errorMessage;

                if (parsedResponse && parsedResponse.http_code) {
                    errorHtml += '<br>HTTP Code: ' + parsedResponse.http_code;
                }

                // Add suggestions if available
                if (parsedResponse && parsedResponse.suggestions && parsedResponse.suggestions.length > 0) {
                    errorHtml += '<br><br><strong>Suggestions:</strong><ul class="mb-0 mt-2">';
                    parsedResponse.suggestions.forEach(function(suggestion) {
                        errorHtml += '<li>' + suggestion + '</li>';
                    });
                    errorHtml += '</ul>';
                }

                testStatus.innerHTML = errorHtml;
                testResponse.style.display = 'none';
                $('#test-status').removeClass('alert-info alert-success').addClass('alert-danger');
            }
        },
        error: function(xhr, status, error) {
            testStatus.innerHTML = '<i class="fa fa-exclamation-triangle text-danger"></i> <strong>Test Failed</strong><br>' +
                'HTTP Error: ' + xhr.status + ' ' + xhr.statusText + '<br>' +
                'Please check your network connection and API configuration.';
            testResponse.style.display = 'none';
            $('#test-status').removeClass('alert-info alert-success').addClass('alert-danger');
        },
        complete: function() {
            testBtn.disabled = false;
        }
    });
}

function viewApiResponse() {
    if (!currentApiResponse) {
        alert_float('warning', 'No API response data available. Please test the API connection first.');
        return;
    }

    // Populate the response viewer modal
    populateResponseViewer(currentApiResponse);

    // Show the modal
    $('#apiResponseModal').modal('show');
}

function escapeHtmlAttribute(value) {
    if (value === null || value === undefined) {
        return '';
    }
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function sanitizeVariableName(rawName, fallback = 'api_response') {
    let sanitized = (rawName || '').trim().replace(/\s+/g, '_').replace(/[^A-Za-z0-9_]/g, '_');
    if (!sanitized) {
        sanitized = fallback;
    }
    if (!/^[A-Za-z_]/.test(sanitized)) {
        sanitized = 'api_' + sanitized;
    }
    if (!sanitized.replace(/_/g, '')) {
        sanitized = fallback;
    }
    return sanitized;
}

function escapeHtml(value) {
    if (value === null || value === undefined) {
        return '';
    }
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function generateResponseVariableId() {
    responseVariableIdCounter += 1;
    return 'rv_' + responseVariableIdCounter;
}

function stripVariablePrefix(path, baseVariable) {
    if (!path) {
        return '';
    }
    if (!baseVariable) {
        return path;
    }
    if (path === baseVariable) {
        return '';
    }
    const prefix = baseVariable + '.';
    if (path.startsWith(prefix)) {
        return path.substring(prefix.length);
    }
    return path;
}

function normalizeGeneratedVariablePaths(definitions, baseVariable) {
    if (!Array.isArray(definitions)) {
        return [];
    }
    return definitions.map(definition => {
        const clone = Object.assign({}, definition);
        const previousBase = definition.source_variable || baseVariable;
        const rawPath = definition.field_path || '';
        let relativePath = stripVariablePrefix(rawPath, previousBase);
        let valueSource = definition.value_source || 'field';

        if (valueSource === 'entire' || relativePath === '' || rawPath === baseVariable) {
            valueSource = 'entire';
            clone.field_path = baseVariable;
            clone.display_path = ENTIRE_RESPONSE_LABEL;
        } else {
            valueSource = 'field';
            clone.field_path = baseVariable ? `${baseVariable}.${relativePath}` : relativePath;
            if (!clone.display_path || clone.display_path === ENTIRE_RESPONSE_LABEL) {
                clone.display_path = relativePath;
            }
        }

        clone.value_source = valueSource;
        clone.source_variable = baseVariable;
        return clone;
    });
}

function updateResponseVariableSummary(node = window.selectedNode) {
    const summaryContainer = document.getElementById('response-variable-summary');
    if (!summaryContainer) {
        return;
    }
    if (!node || !node.data) {
        summaryContainer.innerHTML = '<span class="text-muted small">Select an API trigger to configure response variables.</span>';
        return;
    }
    const baseVariable = node.data.variable_name || (node.data.config ? node.data.config.variable_name : '') || 'api_response';
    const rawChip = `<span class="response-variable-chip"><i class="fa fa-database"></i>{{ ${escapeHtml(baseVariable + '_raw')} }}<span class="text-muted ml-1">Raw response body</span></span>`;
    const statusChip = `<span class="response-variable-chip"><i class="fa fa-signal"></i>{{ ${escapeHtml(baseVariable + '_status')} }}<span class="text-muted ml-1">HTTP code</span></span>`;
    const variables = node.data.config && Array.isArray(node.data.config.generated_variables)
        ? node.data.config.generated_variables
        : [];
    if (!variables.length) {
        summaryContainer.innerHTML = `
            <div class="text-muted small mb-2">No response variables configured yet.</div>
            ${rawChip}${statusChip}
        `;
        return;
    }
    const chips = variables.map(definition => {
        const name = definition.name ? escapeHtml(definition.name) : '';
        const relative = definition.value_source === 'entire'
            ? ENTIRE_RESPONSE_LABEL
            : (definition.display_path || stripVariablePrefix(definition.field_path || '', baseVariable));
        return `<span class="response-variable-chip"><i class="fa fa-tag"></i>{{ ${name} }}<span class="text-muted ml-1">${escapeHtml(relative || '')}</span></span>`;
    });
    chips.push(statusChip, rawChip);
    summaryContainer.innerHTML = chips.join('');
}

function createEmptyResponseVariableRow() {
    return {
        id: generateResponseVariableId(),
        variableName: '',
        valueSource: 'field',
        absolutePath: '',
        displayPath: '',
        fieldType: '',
        sampleValue: null
    };
}

function findResponseVariableRow(rowId) {
    if (!responseVariableEditor || !Array.isArray(responseVariableEditor.rows)) {
        return null;
    }
    return responseVariableEditor.rows.find(row => row.id === rowId) || null;
}

function buildResponseVariableRow(row, fieldOptions) {
    const variableValue = row.variableName ? escapeHtmlAttribute(row.variableName) : '';
    const sourceSelect = RESPONSE_VARIABLE_SOURCE_OPTIONS.map(option => {
        const selected = option.value === row.valueSource ? 'selected' : '';
        return `<option value="${option.value}" ${selected}>${option.label}</option>`;
    }).join('');
    const isEntire = row.valueSource === 'entire';
    const relativePath = isEntire ? '' : (row.displayPath || '');
    const pathValue = escapeHtmlAttribute(relativePath);
    const datalistId = `response-field-options-${row.id}`;
    const datalistHtml = fieldOptions.length
        ? `<datalist id="${datalistId}">
                ${fieldOptions.map(option => {
                    const optionLabelText = option.field_type
                        ? `${option.label} • ${option.field_type}`
                        : option.label;
                    return `<option value="${escapeHtmlAttribute(option.relative)}" label="${escapeHtmlAttribute(optionLabelText)}"></option>`;
                }).join('')}
           </datalist>`
        : `<datalist id="${datalistId}"></datalist>`;
    const hint = isEntire
        ? 'Stores the entire response body.'
        : (fieldOptions.length ? 'Start typing to search response fields by name or path.' : 'Test the API to load response fields, or type a custom path.');
    const sampleHtml = formatSampleValue(row);
    return `
        <div class="response-variable-card" data-row-id="${row.id}">
            <div class="response-variable-row">
                <div class="form-group flex-grow-1">
                    <label class="mb-1">Variable Name</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text">{{</span>
                        </div>
                        <input type="text" class="form-control response-variable-name" data-row-id="${row.id}" placeholder="variable_name" value="${variableValue}">
                        <div class="input-group-append">
                            <span class="input-group-text">}}</span>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-1">Letters, numbers, and underscores only.</small>
                </div>
                <div class="form-group form-group-source">
                    <label class="mb-1">Source</label>
                    <select class="form-control form-control-sm response-variable-source" data-row-id="${row.id}">
                        ${sourceSelect}
                    </select>
                </div>
                <button type="button" class="btn btn-link text-danger response-variable-remove" data-row-id="${row.id}" title="Remove variable">
                    <i class="fa fa-trash"></i>
                </button>
            </div>
            <div class="response-variable-row align-items-stretch">
                <div class="form-group flex-grow-1 mb-0">
                    <label class="mb-1">Response Field</label>
                    <input type="text" class="form-control form-control-sm response-variable-path-input" data-row-id="${row.id}" list="${datalistId}" placeholder="Start typing to search response fields..." value="${pathValue}" ${isEntire ? 'disabled' : ''}>
                    ${datalistHtml}
                    <small class="text-muted d-block mt-1">${escapeHtml(hint)}</small>
                </div>
                <div class="sample-value-container" data-row-id="${row.id}">
                    <span class="sample-label">Sample</span>
                    <div class="sample-value">
                        ${sampleHtml}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderResponseVariablesList() {
    const container = document.getElementById('responseVariablesList');
    if (!container) {
        return;
    }
    const rows = responseVariableEditor.rows || [];
    const fieldOptions = getAvailableResponseFields().sort((a, b) => a.label.localeCompare(b.label));
    availableResponseFieldMap = {};
    fieldOptions.forEach(option => {
        availableResponseFieldMap[option.relative] = option;
    });
    rows.forEach(row => {
        if (!row || row.valueSource !== 'field') {
            return;
        }
        const relative = row.displayPath || '';
        if (!relative) {
            return;
        }
        const option = availableResponseFieldMap[relative];
        if (option) {
            row.fieldType = option.field_type || row.fieldType;
            if (option.sample_value !== undefined) {
                row.sampleValue = option.sample_value;
            }
        }
    });
    if (!rows.length) {
        container.innerHTML = `
            <div class="response-variable-empty">
                Add response variables to capture fields from the API response.
            </div>
        `;
        return;
    }
    container.innerHTML = rows.map(row => buildResponseVariableRow(row, fieldOptions)).join('');

    container.querySelectorAll('.response-variable-name').forEach(input => {
        input.addEventListener('input', handleResponseVariableNameInput);
        input.addEventListener('blur', handleResponseVariableNameInput);
    });
    container.querySelectorAll('.response-variable-source').forEach(select => {
        select.addEventListener('change', handleResponseVariableSourceChange);
    });
    container.querySelectorAll('.response-variable-path-input').forEach(input => {
        input.addEventListener('input', handleResponseFieldInput);
        input.addEventListener('change', handleResponseFieldInput);
    });
    container.querySelectorAll('.response-variable-remove').forEach(button => {
        button.addEventListener('click', handleResponseVariableRemove);
    });
    if (window.selectedNode) {
        updateResponseVariableSummary(window.selectedNode);
    }
}

function flattenResponseStructure(structure, prefix = '', accumulator = []) {
    if (!Array.isArray(structure)) {
        return accumulator;
    }
    structure.forEach(item => {
        if (!item) {
            return;
        }
        const rawPath = item.path || item.name || '';
        if (!rawPath) {
            return;
        }
        const fullPath = item.path || (prefix ? `${prefix}.${rawPath}` : rawPath);
        accumulator.push({
            relative: fullPath,
            label: item.name && item.name !== rawPath ? `${item.name} (${fullPath})` : fullPath,
            sample_value: item.sample_value !== undefined ? item.sample_value : null,
            field_type: item.field_type || item.type || item.sample_type || ''
        });
        if (Array.isArray(item.children) && item.children.length) {
            flattenResponseStructure(item.children, fullPath, accumulator);
        }
    });
    return accumulator;
}

function getAvailableResponseFields() {
    if (!currentApiResponse || !Array.isArray(currentApiResponse.response_structure)) {
        return [];
    }
    const flattened = flattenResponseStructure(currentApiResponse.response_structure);
    const seen = new Set();
    const options = [];
    flattened.forEach(item => {
        if (seen.has(item.relative)) {
            return;
        }
        seen.add(item.relative);
        options.push(item);
    });
    return options;
}

function formatSampleValue(row) {
    if (!row || row.valueSource === 'entire') {
        return '<span class="sample-pill">Entire response body</span>';
    }
    if (row.sampleValue === null || row.sampleValue === undefined || row.sampleValue === '') {
        return '<span class="sample-empty">Not available yet</span>';
    }
    const text = escapeHtml(String(row.sampleValue));
    return `<code>${text.length > 80 ? text.slice(0, 77) + '&hellip;' : text}</code>`;
}
function renderHttpStatusOptions(container, selectedCodes) {
    if (!container) {
        return;
    }
    const selectedSet = new Set((selectedCodes || []).map(code => String(code)));
    const defaults = HTTP_STATUS_OPTIONS.map(opt => ({
        code: String(opt.code),
        label: opt.label
    }));
    const customOptions = Array.from(selectedSet).filter(code => !defaults.find(opt => opt.code === code))
        .map(code => ({ code, label: `${code}` }));
    const merged = [...defaults, ...customOptions];
    container.innerHTML = merged.map(option => {
        const id = `http-status-option-${option.code}`;
        const checked = selectedSet.has(option.code) ? 'checked' : '';
        return `
            <div class="custom-control custom-checkbox status-option">
                <input type="checkbox" class="custom-control-input http-status-option" id="${id}" value="${option.code}" ${checked}>
                <label class="custom-control-label" for="${id}">
                    <span class="status-code">${escapeHtml(option.code)}</span>
                    <span class="status-label">${escapeHtml(option.label)}</span>
                </label>
            </div>
        `;
    }).join('');
}
function updateHttpStatusSummaryDisplay(summaryEl, statuses, enabled) {
    if (!summaryEl) {
        return;
    }
    if (!enabled) {
        summaryEl.innerHTML = '<span class="status-chip muted">Disabled</span>';
        return;
    }
    const uniqueStatuses = Array.from(new Set((statuses || []).map(code => String(code))));
    if (!uniqueStatuses.length) {
        summaryEl.innerHTML = '<span class="status-chip muted">No routes selected</span>';
        return;
    }
    summaryEl.innerHTML = uniqueStatuses.map(code => `<span class="status-chip">${escapeHtml(code)}</span>`).join('');
}
function addCustomHttpStatus(inputEl, optionsContainer, summaryEl, toggleEl) {
    if (!inputEl || !optionsContainer) {
        return;
    }
    const rawValue = inputEl.value.trim();
    if (rawValue === '') {
        return;
    }
    const codeInt = parseInt(rawValue, 10);
    if (Number.isNaN(codeInt) || codeInt < 100 || codeInt > 599) {
        alert_float('warning', 'Enter a valid HTTP status code between 100 and 599.');
        return;
    }
    const code = String(codeInt);
    if (!optionsContainer.querySelector(`.http-status-option[value="${code}"]`)) {
        const wrapper = document.createElement('div');
        wrapper.className = 'custom-control custom-checkbox status-option';
        const id = `http-status-option-${code}`;
        wrapper.innerHTML = `
            <input type="checkbox" class="custom-control-input http-status-option" id="${id}" value="${code}" checked>
            <label class="custom-control-label" for="${id}">
                <span class="status-code">${escapeHtml(code)}</span>
                <span class="status-label">${escapeHtml(code)}</span>
            </label>
        `;
        optionsContainer.appendChild(wrapper);
    }
    const checkbox = optionsContainer.querySelector(`.http-status-option[value="${code}"]`);
    if (checkbox) {
        checkbox.checked = true;
        if (toggleEl && !toggleEl.checked) {
            toggleEl.checked = true;
            toggleEl.dispatchEvent(new Event('change', { bubbles: true }));
        }
        checkbox.dispatchEvent(new Event('change', { bubbles: true }));
    }
    inputEl.value = '';
}
function initializeHttpStatusRouting(node, options = {}) {
    const toggle = document.getElementById('http-status-routing-enabled');
    const optionsContainer = document.getElementById('http-status-options');
    const summaryEl = document.getElementById('http-status-summary');
    const addButton = document.getElementById('http-status-add-btn');
    const addInput = document.getElementById('http-status-custom-input');
    const customEntry = addInput ? addInput.closest('.status-custom-entry') : null;
    if (!toggle || !optionsContainer || !summaryEl) {
        return;
    }
    const preserveExisting = !!options.preserveExisting && optionsContainer.dataset.hydrated === 'true';
    let configRouting = { enabled: false, statuses: [] };
    if (!preserveExisting) {
        configRouting = node && node.data && node.data.config && node.data.config.http_status_routing
            ? node.data.config.http_status_routing
            : { enabled: false, statuses: [] };
        const selectedStatuses = Array.isArray(configRouting.statuses)
            ? configRouting.statuses.map(code => String(code))
            : [];
        renderHttpStatusOptions(optionsContainer, selectedStatuses);
        optionsContainer.dataset.hydrated = 'true';
        toggle.checked = !!configRouting.enabled;
    }
    const updateState = () => {
        const enabled = toggle.checked;
        optionsContainer.classList.toggle('is-disabled', !enabled);
        optionsContainer.style.display = enabled ? 'grid' : 'none';
        const checkboxes = optionsContainer.querySelectorAll('.http-status-option');
        checkboxes.forEach(cb => {
            cb.disabled = !enabled;
        });
        if (addInput) {
            addInput.disabled = !enabled;
        }
        if (customEntry) {
            customEntry.style.display = enabled ? '' : 'none';
        }
        if (addButton) {
            addButton.disabled = !enabled;
        }
        const statuses = enabled
            ? Array.from(optionsContainer.querySelectorAll('.http-status-option:checked')).map(cb => cb.value)
            : [];
        updateHttpStatusSummaryDisplay(summaryEl, statuses, enabled);
    };
    if (!toggle.dataset.initialized) {
        toggle.addEventListener('change', updateState);
        optionsContainer.addEventListener('change', updateState);
        if (addButton && addInput) {
            addButton.addEventListener('click', () => addCustomHttpStatus(addInput, optionsContainer, summaryEl, toggle));
            addInput.addEventListener('keyup', (event) => {
                if (event.key === 'Enter') {
                    addCustomHttpStatus(addInput, optionsContainer, summaryEl, toggle);
                }
            });
        }
        toggle.dataset.initialized = 'true';
    }
    updateState();
}

function initializeConditionConfig(node) {
    const config = (node && node.data && node.data.config) ? node.data.config : {};
    const fieldInput = document.getElementById('condition-field');
    const operatorSelect = document.getElementById('condition-operator');
    const valueInput = document.getElementById('condition-value');
    const datalist = document.getElementById('condition-field-options');
    if (fieldInput) {
        fieldInput.value = config.field || '';
    }
    if (operatorSelect) {
        operatorSelect.value = config.operator || 'equals';
    }
    if (valueInput) {
        valueInput.value = config.value || '';
    }
    if (datalist) {
        const options = getAvailableResponseFields().sort((a, b) => a.label.localeCompare(b.label));
        datalist.innerHTML = options.map(option => {
            const optionLabelText = option.field_type
                ? `${option.label} • ${option.field_type}`
                : option.label;
            return `<option value="${escapeHtmlAttribute(option.relative)}" label="${escapeHtmlAttribute(optionLabelText)}"></option>`;
        }).join('');
    }
    const pillContainer = document.getElementById('condition-operator-pills');
    if (pillContainer && operatorSelect) {
        const updatePillState = () => {
            pillContainer.querySelectorAll('.operator-pill').forEach(pill => {
                pill.classList.toggle('is-active', pill.dataset.operator === operatorSelect.value);
            });
        };
        pillContainer.querySelectorAll('.operator-pill').forEach(pill => {
            pill.addEventListener('click', () => {
                operatorSelect.value = pill.dataset.operator;
                updatePillState();
            });
        });
        operatorSelect.addEventListener('change', updatePillState);
        updatePillState();
    }
    const quickContainer = document.getElementById('condition-quick-values');
    if (quickContainer && valueInput) {
        quickContainer.querySelectorAll('.quick-value-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                valueInput.value = chip.dataset.value;
            });
        });
    }
}

function ensureResponseVariablesPanel() {
    const host = document.getElementById('response-variable-editor');
    if (!host) {
        return null;
    }
    let panel = host.querySelector('.response-variable-panel');
    if (panel) {
        return panel;
    }
    const template = document.getElementById('responseVariablesPanelTemplate');
    if (!template || !template.content || !template.content.firstElementChild) {
        console.warn('Response variables panel template not found');
        return null;
    }
    panel = template.content.firstElementChild.cloneNode(true);
    host.innerHTML = '';
    host.appendChild(panel);
    return panel;
}

function closeResponseFieldPicker() {
    // No overlay picker in the inline editor; function retained for compatibility.
}

function closeResponseVariablesPanel() {
    closeResponseFieldPicker();
    if (window.selectedNode) {
        updateResponseVariableSummary(window.selectedNode);
    }
}

function openResponseVariablesModal() {
    if (!window.selectedNode || !window.selectedNode.data || window.selectedNode.data.type !== 'api_trigger') {
        alert_float('warning', 'Please select an API trigger node first.');
        return;
    }
    const variableInput = document.getElementById('api-trigger-variable');
    let baseVariable = window.selectedNode.data.variable_name || (window.selectedNode.data.config ? window.selectedNode.data.config.variable_name : '') || 'api_response';
    baseVariable = sanitizeVariableName(variableInput ? variableInput.value : baseVariable, 'api_response');
    if (variableInput) {
        variableInput.value = baseVariable;
    }
    if (!window.flowVariables) {
        window.flowVariables = {};
    }
    if (!Object.prototype.hasOwnProperty.call(window.flowVariables, baseVariable)) {
        window.flowVariables[baseVariable] = null;
    }
    const rawKey = baseVariable + '_raw';
    if (!Object.prototype.hasOwnProperty.call(window.flowVariables, rawKey)) {
        window.flowVariables[rawKey] = null;
    }
    responseVariableIdCounter = 0;
    const existing = normalizeGeneratedVariablePaths(
        window.selectedNode.data.config && Array.isArray(window.selectedNode.data.config.generated_variables)
            ? window.selectedNode.data.config.generated_variables
            : [],
        baseVariable
    );
    responseVariableEditor = {
        baseVariable,
        rows: existing.length
            ? existing.map(definition => ({
                id: generateResponseVariableId(),
                variableName: definition.name || '',
                valueSource: definition.value_source || (definition.field_path === baseVariable ? 'entire' : 'field'),
                absolutePath: definition.field_path || baseVariable,
                displayPath: definition.value_source === 'entire'
                    ? ENTIRE_RESPONSE_LABEL
                    : (definition.display_path || stripVariablePrefix(definition.field_path || '', baseVariable)),
                fieldType: definition.field_type || '',
                sampleValue: definition.sample_value !== undefined ? definition.sample_value : null
            }))
            : [createEmptyResponseVariableRow()]
    };
    ensureResponseVariablesPanel();
    renderResponseVariablesList();
}

function addResponseVariableRow() {
    if (!responseVariableEditor) {
        responseVariableEditor = { baseVariable: 'api_response', rows: [] };
    }
    responseVariableEditor.rows.push(createEmptyResponseVariableRow());
    renderResponseVariablesList();
}

function removeResponseVariableRow(rowId) {
    if (!responseVariableEditor || !Array.isArray(responseVariableEditor.rows)) {
        return;
    }
    responseVariableEditor.rows = responseVariableEditor.rows.filter(row => row.id !== rowId);
    if (!responseVariableEditor.rows.length) {
        responseVariableEditor.rows.push(createEmptyResponseVariableRow());
    }
    renderResponseVariablesList();
}

function handleResponseVariableRemove(event) {
    const rowId = event.currentTarget.dataset.rowId;
    removeResponseVariableRow(rowId);
}

function handleResponseVariableNameInput(event) {
    const rowId = event.target.dataset.rowId;
    const row = findResponseVariableRow(rowId);
    if (!row) {
        return;
    }
    const rawValue = event.target.value;
    if (!rawValue.trim()) {
        row.variableName = '';
        event.target.value = '';
        return;
    }
    const sanitized = sanitizeVariableName(rawValue, 'var_' + row.id.replace(/\D/g, ''));
    row.variableName = sanitized;
    event.target.value = sanitized;
}

function handleResponseVariableSourceChange(event) {
    const rowId = event.target.dataset.rowId;
    const row = findResponseVariableRow(rowId);
    if (!row) {
        return;
    }
    row.valueSource = event.target.value;
    if (row.valueSource === 'entire') {
        row.absolutePath = responseVariableEditor.baseVariable;
        row.displayPath = ENTIRE_RESPONSE_LABEL;
        row.sampleValue = null;
    } else {
        if (row.absolutePath === responseVariableEditor.baseVariable) {
            row.absolutePath = '';
        }
        if (row.displayPath === ENTIRE_RESPONSE_LABEL) {
            row.displayPath = '';
        }
    }
    renderResponseVariablesList();
}

function handleResponseFieldInput(event) {
    const input = event.target;
    const rowId = input.dataset.rowId;
    const row = findResponseVariableRow(rowId);
    if (!row || row.valueSource === 'entire') {
        return;
    }
    const baseVariable = responseVariableEditor.baseVariable || 'api_response';
    let value = input.value.trim();
    if (value.startsWith(baseVariable + '.')) {
        value = value.substring(baseVariable.length + 1);
        input.value = value;
    }
    if (value === '') {
        row.displayPath = '';
        row.absolutePath = '';
        row.fieldType = '';
        row.sampleValue = null;
    } else {
        const option = availableResponseFieldMap[value];
        if (option) {
            row.displayPath = option.relative;
            row.absolutePath = baseVariable ? `${baseVariable}.${option.relative}` : option.relative;
            row.fieldType = option.field_type || '';
            row.sampleValue = option.sample_value !== undefined ? option.sample_value : null;
        } else {
            row.displayPath = value;
            row.absolutePath = baseVariable ? `${baseVariable}.${value}` : value;
            row.fieldType = '';
            row.sampleValue = null;
        }
    }
    const sampleNode = document.querySelector(`.sample-value-container[data-row-id="${rowId}"] .sample-value`);
    if (sampleNode) {
        sampleNode.innerHTML = formatSampleValue(row);
    }
}

function saveResponseVariables() {
    if (!window.selectedNode || !window.selectedNode.data || window.selectedNode.data.type !== 'api_trigger') {
        alert_float('warning', 'Please select an API trigger node first.');
        return;
    }
    const variableInput = document.getElementById('api-trigger-variable');
    let baseVariable = responseVariableEditor.baseVariable || window.selectedNode.data.variable_name || (window.selectedNode.data.config ? window.selectedNode.data.config.variable_name : '') || 'api_response';
    baseVariable = sanitizeVariableName(baseVariable, 'api_response');
    if (variableInput) {
        variableInput.value = baseVariable;
    }
    responseVariableEditor.baseVariable = baseVariable;
    const rows = responseVariableEditor.rows || [];
    const usedNames = new Set();
    const errors = [];
    const definitions = rows.map(row => {
        const name = row.variableName ? sanitizeVariableName(row.variableName, row.variableName) : '';
        const isEmptyRow = !name && !row.absolutePath;
        if (isEmptyRow) {
            return null;
        }
        if (!name) {
            errors.push('Each response variable needs a variable name.');
            return null;
        }
        if (usedNames.has(name)) {
            errors.push(`Duplicate variable name "${name}". Please use unique names.`);
            return null;
        }
        usedNames.add(name);
        let absolutePath = row.valueSource === 'entire'
            ? baseVariable
            : (row.absolutePath || (row.displayPath ? `${baseVariable}.${row.displayPath}` : ''));
        if (!absolutePath) {
            errors.push(`Please select a response field for "{{ ${name} }}".`);
            return null;
        }
        absolutePath = row.valueSource === 'entire'
            ? baseVariable
            : absolutePath;
        return {
            name: name,
            field_path: absolutePath,
            field_type: row.fieldType || '',
            sample_value: row.sampleValue !== undefined ? row.sampleValue : null,
            source_variable: baseVariable,
            display_path: row.valueSource === 'entire' ? ENTIRE_RESPONSE_LABEL : (row.displayPath || stripVariablePrefix(absolutePath, baseVariable)),
            value_source: row.valueSource || 'field'
        };
    }).filter(definition => definition !== null);

    if (errors.length) {
        alert_float('warning', errors[0]);
        return;
    }

    if (!window.selectedNode.data.config) {
        window.selectedNode.data.config = {};
    }
    window.selectedNode.data.variable_name = baseVariable;
    window.selectedNode.data.config.variable_name = baseVariable;
    window.selectedNode.data.config.generated_variables = normalizeGeneratedVariablePaths(definitions, baseVariable);

    const applyVariableChanges = (nodeList) => nodeList.map(n => {
        if (!n || n.id !== window.selectedNode.id) {
            return n;
        }
        return {
            ...n,
            data: {
                ...n.data,
                variable_name: baseVariable,
                config: {
                    ...(window.selectedNode.data.config || {})
                }
            }
        };
    });

    if (typeof window.setNodes === 'function') {
        window.setNodes(nodes => {
            const updatedNodes = applyVariableChanges(nodes);
            window.nodes = updatedNodes;
            syncSelectedNodeReference(updatedNodes);
            return updatedNodes;
        });
    } else if (Array.isArray(window.nodes)) {
        window.nodes = applyVariableChanges(window.nodes);
        syncSelectedNodeReference(window.nodes);
    }

    if (!window.flowVariables) {
        window.flowVariables = {};
    }
    window.flowVariables[baseVariable] = window.flowVariables[baseVariable] || null;
    const rawVariableKey = baseVariable + '_raw';
    if (!Object.prototype.hasOwnProperty.call(window.flowVariables, rawVariableKey)) {
        window.flowVariables[rawVariableKey] = null;
    }
    window.selectedNode.data.config.generated_variables.forEach(definition => {
        if (!Object.prototype.hasOwnProperty.call(window.flowVariables, definition.name)) {
            window.flowVariables[definition.name] = definition.sample_value ?? null;
        }
    });

    updateResponseVariableSummary(window.selectedNode);

    if (currentApiResponse && currentApiResponse.decoded_response) {
        refreshDerivedVariables(baseVariable, currentApiResponse.decoded_response);
    }

    alert_float('success', 'Response variables saved!');
    renderResponseVariablesList();
    renderSelectedNodeSummary(window.selectedNode || null);
}

function populateResponseViewer(apiResponse) {
    // Clear existing content
    const structureContainer = document.getElementById('responseStructureContainer');
    const rawResponseContainer = document.getElementById('rawResponseText');
    const variablesContainer = document.getElementById('variablesMappingContainer');

    if (!structureContainer || !rawResponseContainer || !variablesContainer) {
        return;
    }

    // Set modal title
    const modalTitle = document.getElementById('apiResponseTitle');
    if (modalTitle) {
        modalTitle.textContent = 'API Response Details (HTTP ' + apiResponse.http_code + ')';
    }

    // Populate raw response
    rawResponseContainer.textContent = apiResponse.raw_response;

    // Populate response structure
    if (apiResponse.response_structure && apiResponse.response_structure.length > 0) {
        const structureHtml = buildStructureHtml(apiResponse.response_structure);
        structureContainer.innerHTML = structureHtml;
    } else {
        structureContainer.innerHTML = '<div class="alert alert-info">No structured data available for this response.</div>';
    }

    // Populate variables mapping section (keep existing guide)
    variablesContainer.innerHTML = `
        <div class="alert alert-info">
            <h6><i class="fa fa-info-circle"></i> Variable Creation Guide</h6>
            <p>This feature allows you to extract specific fields from the API response and create reusable variables for use throughout your flow.</p>
            <p><strong>How to create variables:</strong></p>
            <ol>
                <li>Click on any field in the response structure above</li>
                <li>Give it a meaningful variable name (e.g., "user_id", "customer_name")</li>
                <li>The variable will be available in all downstream nodes as <code>{{variable_name}}</code></li>
            </ol>
        </div>
    `;
}

function buildStructureHtml(structure, level = 0) {
    if (!structure || !Array.isArray(structure)) {
        return '<div class="response-item">No data available</div>';
    }

    let html = '<div class="response-structure">';

    structure.forEach(item => {
        if (!item) return;

        const indentClass = 'response-item-indent-' + level;
        const typeClass = 'response-item-' + (item.type || 'unknown');

        if (item.type === 'field') {
            const fieldPath = item.path || item.name;
            const hasSampleValue = item.sample_value !== undefined && item.sample_value !== null;
            const sampleValueText = hasSampleValue ? String(item.sample_value) : '';
            const sampleValue = sampleValueText !== '' ? ': <span class="response-sample-value">' + sampleValueText + '</span>' : '';
            const typeBadge = '<span class="badge badge-secondary badge-sm">' + (item.field_type || 'unknown') + '</span>';
            const encodedSampleValue = hasSampleValue ? encodeURIComponent(sampleValueText) : '';

            html += `
                <div class="response-item ${indentClass} ${typeClass}"
                    data-field-path="${escapeHtmlAttribute(fieldPath)}"
                    data-display-name="${escapeHtmlAttribute(item.name)}"
                    data-field-type="${escapeHtmlAttribute(item.field_type || '')}"
                    data-sample-value="${encodedSampleValue}"
                    onclick="selectResponseField(event)">
                    <div class="response-field-header">
                        <i class="fa fa-file-o response-field-icon"></i>
                        <span class="response-field-name">${item.name}</span>
                        ${typeBadge}
                        ${sampleValue}
                        <small class="response-description">${item.description || ''}</small>
                    </div>
                </div>
            `;

            // Add nested children if any
            if (item.children && item.children.length > 0 && level < 2) {
                html += buildStructureHtml(item.children, level + 1);
            }
        } else if (item.type === 'array') {
            const sampleType = item.sample_type ? '<span class="badge badge-primary badge-sm">' + item.sample_type + '[]</span>' : '';

            html += `
                <div class="response-item ${indentClass} ${typeClass}">
                    <div class="response-field-header">
                        <i class="fa fa-list response-field-icon"></i>
                        <span class="response-field-name">${item.name}</span>
                        ${sampleType}
                        <small class="response-description">${item.description}</small>
                    </div>
                </div>
            `;

            // Add array children if any
            if (item.children && item.children.length > 0 && level < 2) {
                html += buildStructureHtml(item.children, level + 1);
            }
        }
    });

    html += '</div>';
    return html;
}

function selectResponseField(domEvent) {
    // Highlight the selected field
    document.querySelectorAll('.response-item').forEach(item => {
        item.classList.remove('selected');
    });
    if (domEvent && domEvent.currentTarget) {
        domEvent.currentTarget.classList.add('selected');
    }

    const target = domEvent ? domEvent.currentTarget : null;
    if (!target) {
        return;
    }

    const fieldPath = target.getAttribute('data-field-path');
    const displayName = target.getAttribute('data-display-name') || fieldPath;
    const fieldType = target.getAttribute('data-field-type') || 'unknown';
    const encodedSample = target.getAttribute('data-sample-value') || '';
    const sampleValue = encodedSample ? decodeURIComponent(encodedSample) : '';

    // Store selected field for variable creation
    window.selectedResponseField = {
        name: fieldPath,
        displayName: displayName,
        type: fieldType,
        sampleValue: sampleValue
    };

    // Update variable creation section
    const variablesContainer = document.getElementById('variablesMappingContainer');
    if (variablesContainer) {
        variablesContainer.innerHTML = `
            <div class="alert alert-success">
                <h6><i class="fa fa-check-circle"></i> Field Selected</h6>
                <p><strong>Field:</strong> <code>${fieldName}</code></p>
                <p><strong>Type:</strong> ${fieldType}</p>
                ${sampleValue ? `<p><strong>Sample:</strong> <code>${sampleValue}</code></p>` : ''}
                <button type="button" class="btn btn-primary btn-sm" onclick="createVariableFromResponse()">
                    <i class="fa fa-plus"></i> Create Variable from this Field
                </button>
            </div>
        `;
    }
}

function createVariableFromResponse() {
    if (!window.selectedResponseField) {
        alert_float('warning', 'Please select a field from the response structure first.');
        return;
    }

    const fieldPath = window.selectedResponseField.name;
    const displayPath = window.selectedResponseField.displayName || fieldPath;
    const fieldType = window.selectedResponseField.type;

    // Suggest a variable name based on the field
    const suggestedName = suggestVariableName(displayPath);

    // Prompt for variable name
    const variableName = prompt('Enter a name for this variable (will be available as {{variable_name}}):', suggestedName);

    if (!variableName || !variableName.trim()) {
        return;
    }

    // Sanitize variable name
    const sanitizedName = variableName.trim().toLowerCase().replace(/[^a-z0-9_]/g, '_').replace(/^[^a-z]/, 'var_$&');

    const varInput = document.getElementById('api-trigger-variable');
    const baseVariable = sanitizeVariableName(varInput ? varInput.value : 'api_response');
    const absolutePath = fieldPath && fieldPath.startsWith(baseVariable)
        ? fieldPath
        : baseVariable + (fieldPath ? '.' + fieldPath : '');

    if (!window.selectedNode || !window.selectedNode.data) {
        alert_float('warning', 'Unable to associate variable with the current node.');
        return;
    }

    if (!window.selectedNode.data.config) {
        window.selectedNode.data.config = {};
    }

    if (!Array.isArray(window.selectedNode.data.config.generated_variables)) {
        window.selectedNode.data.config.generated_variables = [];
    }

    const variableDefinition = {
        name: sanitizedName,
        field_path: absolutePath,
        field_type: fieldType,
        sample_value: window.selectedResponseField.sampleValue || null,
        source_variable: baseVariable,
        display_path: displayPath,
        value_source: 'field'
    };

    const existingIndex = window.selectedNode.data.config.generated_variables.findIndex(v => v.name === sanitizedName);
    if (existingIndex >= 0) {
        window.selectedNode.data.config.generated_variables[existingIndex] = variableDefinition;
    } else {
        window.selectedNode.data.config.generated_variables.push(variableDefinition);
    }

    if (!window.flowVariables) {
        window.flowVariables = {};
    }

    window.selectedNode.data.variable_name = baseVariable;
    window.selectedNode.data.config.variable_name = baseVariable;
    window.selectedNode.data.config.generated_variables = normalizeGeneratedVariablePaths(window.selectedNode.data.config.generated_variables, baseVariable);
    if (typeof window.setNodes === 'function') {
        window.setNodes(nodes => nodes.map(n => {
            if (!n || n.id !== window.selectedNode.id) {
                return n;
            }
            return {
                ...n,
                data: {
                    ...n.data,
                    variable_name: baseVariable,
                    config: {
                        ...(window.selectedNode.data.config || {})
                    }
                }
            };
        }));
    }
    window.flowVariables[baseVariable] = window.flowVariables[baseVariable] || null;
    updateResponseVariableSummary(window.selectedNode);

    const contextData = {};
    if (currentApiResponse && currentApiResponse.decoded_response) {
        contextData[baseVariable] = currentApiResponse.decoded_response;
    }
    const resolvedValue = resolveValueByPath(contextData, absolutePath);
    window.flowVariables[sanitizedName] = resolvedValue !== undefined && resolvedValue !== null
        ? resolvedValue
        : (window.selectedResponseField.sampleValue || null);

    refreshDerivedVariables(baseVariable, currentApiResponse ? currentApiResponse.decoded_response : undefined);

    alert_float('success', `Variable '{{${sanitizedName}}}' created! Use it in downstream nodes.`);

    // Close the modal
    $('#apiResponseModal').modal('hide');
    renderSelectedNodeSummary(window.selectedNode || null);
}

function suggestVariableName(fieldName) {
    // Convert field path to variable name
    const parts = fieldName.split('.');
    const lastPart = parts[parts.length - 1];

    // Remove array indices [0], [1], etc.
    let cleanName = lastPart.replace(/\[\d+\]$/, '');

    // Convert camelCase/snake_case to reasonable variable name
    cleanName = cleanName.replace(/([a-z])([A-Z])/g, '$1_$2').toLowerCase();

    // Ensure it starts with letter or underscore
    if (!/^[a-z_]/.test(cleanName)) {
        cleanName = 'var_' + cleanName;
    }

    return cleanName;
}

function showVariablePicker(target) {
    selectedVariableTarget = target;
    if (typeof selectedVariableTarget === 'string') {
        const resolved = document.getElementById(selectedVariableTarget);
        if (resolved) {
            selectedVariableTarget = resolved;
        }
    }
    populateVariablePicker();
    $('#variablePickerModal').modal('show');
}
function showVariablePickerForMapping(button) {
    const input = button.closest('.mapping-row').querySelector('.mapping-source');
    selectedVariableTarget = input;
    populateVariablePicker();
    $('#variablePickerModal').modal('show');
}
function populateVariablePicker() {
    const variableList = document.getElementById('variable-list');
    const variablePreview = document.getElementById('variable-preview');
    const variables = window.flowVariables || {};
    const nodes = window.nodes || [];

    if (!variableList || !variablePreview) {
        return;
    }

    // Clear existing content
    variableList.innerHTML = '';
    variablePreview.textContent = 'Select a variable to see its structure';

    if (Object.keys(variables).length === 0) {
        variableList.innerHTML = '<div class="text-muted p-3 text-center">No variables available. Add an API trigger node first.</div>';
        return;
    }

    // Add available variables
    Object.keys(variables).forEach(varName => {
        const varItem = document.createElement('div');
        varItem.className = 'variable-item';
        varItem.innerHTML = `
            <div class="variable-item-header" onclick="selectVariable('${varName}')">
                <strong>${varName}</strong>
                <small class="text-muted">Click to select</small>
            </div>
        `;
        variableList.appendChild(varItem);
    });
}
function selectVariable(variableName) {
    const variablePreview = document.getElementById('variable-preview');
    if (!variablePreview) {
        return;
    }

    // Show variable structure preview
    const variables = window.flowVariables || {};
    const varData = variables[variableName];

    if (varData === null) {
        variablePreview.innerHTML = `
            <div class="alert alert-info">
                <strong>${variableName}</strong> - Variable will be populated when API is executed
            </div>
        `;
    } else if (typeof varData === 'object') {
        const structure = generateObjectStructure(varData);
        variablePreview.innerHTML = `
            <div class="variable-structure">
                <strong>${variableName}</strong> structure:
                <pre>${structure}</pre>
            </div>
        `;
    } else {
        variablePreview.innerHTML = `
            <div class="alert alert-success">
                <strong>${variableName}:</strong> ${varData}
            </div>
        `;
    }

    // Highlight selected variable
    document.querySelectorAll('.variable-item').forEach(item => {
        item.classList.remove('selected');
    });
    event.currentTarget.closest('.variable-item').classList.add('selected');
}
function generateObjectStructure(obj, indent = 0) {
    let structure = '';
    const spaces = '  '.repeat(indent);

    if (obj === null || obj === undefined) {
        return 'null';
    }

    if (typeof obj === 'object') {
        if (Array.isArray(obj)) {
            structure += '[\n';
            obj.forEach((item, index) => {
                structure += `${spaces}  [${index}]: ${generateObjectStructure(item, indent + 1)}\n`;
            });
            structure += `${spaces}]`;
        } else {
            structure += '{\n';
            Object.keys(obj).forEach(key => {
                structure += `${spaces}  ${key}: ${generateObjectStructure(obj[key], indent + 1)}\n`;
            });
            structure += `${spaces}}`;
        }
    } else {
        structure += typeof obj === 'string' ? `"${obj}"` : String(obj);
    }

    return structure;
}
function insertSelectedVariable() {
    if (!selectedVariableTarget) {
        return;
    }

    let target = selectedVariableTarget;
    if (typeof target === 'string') {
        target = document.getElementById(target);
    }

    if (!target) {
        alert_float('danger', 'Unable to find the field to insert the variable.');
        return;
    }

    const variableList = document.getElementById('variable-list');
    const selectedItem = variableList.querySelector('.variable-item.selected');

    if (!selectedItem) {
        alert('Please select a variable first');
        return;
    }

    const variableName = selectedItem.querySelector('strong').textContent;
    const currentValue = target.value || '';

    // Insert variable reference at cursor position or at the end
    const cursorPosition = typeof target.selectionStart === 'number' ? target.selectionStart : currentValue.length;
    const beforeCursor = currentValue.substring(0, cursorPosition);
    const afterCursor = currentValue.substring(cursorPosition);

    // Check if we're inserting a variable reference
    const variableReference = `{{${variableName}}}`;

    // If text is selected, replace it
    if (typeof target.selectionStart === 'number' && target.selectionStart !== target.selectionEnd) {
        const start = target.selectionStart;
        const end = target.selectionEnd;
        const newValue = beforeCursor.substring(0, start) + variableReference + afterCursor.substring(end - start);
        target.value = newValue;
    } else {
        // Insert at cursor position
        target.value = beforeCursor + variableReference + afterCursor;
    }

    // Trigger input event to update any dependent logic
    const event = new Event('input', { bubbles: true });
    target.dispatchEvent(event);

    selectedVariableTarget = target;

    $('#variablePickerModal').modal('hide');
}
function resolveValueByPath(context, path) {
    if (!context || !path) {
        return undefined;
    }
    const normalizedPath = path.replace(/\[(\d+)\]/g, '.$1');
    const segments = normalizedPath.split('.').filter(Boolean);
    let current = context;
    for (const segment of segments) {
        if (current === undefined || current === null) {
            return undefined;
        }
        if (Array.isArray(current)) {
            const index = parseInt(segment, 10);
            if (Number.isNaN(index) || index < 0 || index >= current.length) {
                return undefined;
            }
            current = current[index];
            continue;
        }
        if (typeof current === 'object' && segment in current) {
            current = current[segment];
            continue;
        }
        return undefined;
    }
    return current;
}
function refreshDerivedVariables(baseVariable, decodedResponse) {
    if (!baseVariable) {
        return;
    }
    const node = window.selectedNode;
    if (!node || !node.data || !node.data.config) {
        return;
    }
    const definitions = node.data.config.generated_variables;
    if (!Array.isArray(definitions) || definitions.length === 0) {
        return;
    }
    if (!window.flowVariables) {
        window.flowVariables = {};
    }
    const context = {};
    if (decodedResponse !== undefined) {
        context[baseVariable] = decodedResponse;
        window.flowVariables[baseVariable] = decodedResponse;
    }
    const rawKey = baseVariable + '_raw';
    if (!Object.prototype.hasOwnProperty.call(window.flowVariables, rawKey)) {
        window.flowVariables[rawKey] = null;
    }
    if (currentApiResponse && Object.prototype.hasOwnProperty.call(currentApiResponse, 'raw_response')) {
        window.flowVariables[rawKey] = currentApiResponse.raw_response;
        context[rawKey] = currentApiResponse.raw_response;
    }
    definitions.forEach(definition => {
        if (!definition || !definition.name) {
            return;
        }
        const targetPath = definition.field_path || '';
        const absolutePath = targetPath && targetPath.startsWith(baseVariable)
            ? targetPath
            : (baseVariable && targetPath ? baseVariable + '.' + targetPath : targetPath);
        const resolved = resolveValueByPath(context, absolutePath);
        const fallback = definition.sample_value !== undefined ? definition.sample_value : null;
        window.flowVariables[definition.name] = resolved !== undefined && resolved !== null ? resolved : fallback;
    });
}
function resolveVariableReferences(text, context = {}) {
    if (!text || typeof text !== 'string') {
        return text;
    }

    // Pattern to match {{variable_name}} or {{variable_name.field}} or {{variable_name.field.subfield}}
    const variablePattern = /\{\{([^}]+)\}\}/g;

    return text.replace(variablePattern, (match, variablePath) => {
        const trimmedPath = variablePath.trim();
        const resolvedValue = resolveValueByPath(context, trimmedPath);
        if (resolvedValue === undefined) {
            console.warn(`Unable to resolve variable path '${trimmedPath}'`);
            return match;
        }
        return resolvedValue !== null ? String(resolvedValue) : '';
    });
}
function updateVariableStorage(apiResponse, variableName) {
    if (!variableName) {
        return;
    }
    if (!window.flowVariables) {
        window.flowVariables = {};
    }
    window.flowVariables[variableName] = apiResponse;
    const rawKey = variableName + '_raw';
    if (!Object.prototype.hasOwnProperty.call(window.flowVariables, rawKey)) {
        window.flowVariables[rawKey] = null;
    }
    if (currentApiResponse && Object.prototype.hasOwnProperty.call(currentApiResponse, 'raw_response')) {
        window.flowVariables[rawKey] = currentApiResponse.raw_response;
    }
    if (currentApiResponse && Object.prototype.hasOwnProperty.call(currentApiResponse, 'http_code')) {
        const statusKey = variableName + '_status';
        window.flowVariables[statusKey] = currentApiResponse.http_code;
        window.flowVariables.http_status_code = currentApiResponse.http_code;
        const httpRouting = window.selectedNode && window.selectedNode.data && window.selectedNode.data.config
            ? window.selectedNode.data.config.http_status_routing || {}
            : {};
        let routeValue = currentApiResponse.http_code !== null && currentApiResponse.http_code !== undefined
            ? String(currentApiResponse.http_code)
            : 'unknown';
        if (httpRouting.enabled) {
            const configured = Array.isArray(httpRouting.statuses)
                ? httpRouting.statuses.map(code => String(code))
                : [];
            if (!configured.includes(routeValue)) {
                routeValue = 'default';
            }
        }
        window.flowVariables.http_status_route = routeValue;
    }
    console.log(`Stored API response in variable: ${variableName}`, apiResponse);
}
function getAllVariables() {
    return window.flowVariables || {};
}
function clearAllVariables() {
    window.flowVariables = {};
    console.log('Cleared all flow variables');
}
// Make variable functions globally available
window.showVariablePicker = showVariablePicker;
window.showVariablePickerForMapping = showVariablePickerForMapping;
window.insertSelectedVariable = insertSelectedVariable;
window.resolveVariableReferences = resolveVariableReferences;
window.updateVariableStorage = updateVariableStorage;
window.getAllVariables = getAllVariables;
window.clearAllVariables = clearAllVariables;
window.refreshDerivedVariables = refreshDerivedVariables;
window.resolveValueByPath = resolveValueByPath;
window.openResponseVariablesModal = openResponseVariablesModal;
window.closeResponseVariablesPanel = closeResponseVariablesPanel;
window.closeResponseFieldPicker = closeResponseFieldPicker;
window.addResponseVariableRow = addResponseVariableRow;
window.saveResponseVariables = saveResponseVariables;
window.updateResponseVariableSummary = updateResponseVariableSummary;

// Ensure stacked modals (e.g. Response Variables over Node Config) display correctly
$(document).on('show.bs.modal', '.modal', function () {
    const visibleCount = $('.modal:visible').length;
    const zIndex = 1040 + (10 * visibleCount);
    $(this).css('z-index', zIndex);
    setTimeout(function () {
        $('.modal-backdrop').not('.modal-stack')
            .css('z-index', zIndex - 1)
            .addClass('modal-stack');
    }, 0);
});

$(document).on('hidden.bs.modal', '.modal', function () {
    if ($('.modal:visible').length > 0) {
        $('body').addClass('modal-open');
    }
    $('.modal-backdrop.modal-stack').removeClass('modal-stack');
});
</script>
<?php init_tail(); ?>
</body>
</html>
