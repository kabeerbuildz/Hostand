<div class="modal fade" id="ticketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="ti ti-ticket me-2"></i>{{ __('Ticket Details') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="max-height: 80vh; overflow-y: auto;">
                <div id="ticketModalContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <small class="text-muted">{{ __('Ticket Support System') }}</small>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="ti ti-x me-1"></i>{{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#ticketModal .modal-dialog {
    max-width: 95vw;
    margin: 1.75rem auto;
}

#ticketModal .modal-content {
    max-height: 90vh;
    overflow: hidden;
}

#ticketModal .modal-body {
    overflow-y: auto;
    overflow-x: hidden;
}

#ticketModalContent {
    position: relative;
    z-index: 1;
}

/* Discussion box scrolling styles */
#repliesContainer {
    scrollbar-width: thin;
    scrollbar-color: #dee2e6 #f8f9fa;
}

#repliesContainer::-webkit-scrollbar {
    width: 6px;
}

#repliesContainer::-webkit-scrollbar-track {
    background: #f8f9fa;
    border-radius: 3px;
}

#repliesContainer::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 3px;
}

#repliesContainer::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}

/* Smooth scrolling for discussion box */
.card-body[style*="overflow-y: auto"] {
    scroll-behavior: smooth;
}

/* Discussion item hover effects */
.reply-item:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}

/* Empty state styling */
.empty-state {
    min-height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}
</style>

<script>
// Wait for both DOM and jQuery to be ready
function initTicketModal() {
    if (typeof $ === 'undefined') {
        // If jQuery is not available, wait a bit and try again
        setTimeout(initTicketModal, 100);
        return;
    }
    
    $(document).ready(function() {
    // Handle view ticket modal
    $('.view-ticket-modal').on('click', function(e) {
        e.preventDefault();
        var ticketId = $(this).data('ticket-id');
        loadTicketModal(ticketId);
    });

    // Handle modal close to delete viewed images
    $('#ticketModal').on('hidden.bs.modal', function() {
        // The images are automatically deleted when viewed via the viewPhoto route
        // This is handled server-side in the SupportController
    });

    function loadTicketModal(ticketId) {
        // Store ticket ID for reply form
        $('#ticketModal').data('ticket-id', ticketId);
        
        // Clear previous content
        $('#ticketModalContent').empty();
        
        // Show loading state
        $('#ticketModalContent').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading ticket details...</p>
            </div>
        `);
        
        $.ajax({
            url: '{{ route("support.showModal", ":id") }}'.replace(':id', ticketId),
            type: 'GET',
            success: function(response) {
                var support = response.support;
                var canEdit = response.canEdit;
                var canReply = response.canReply;
                var user = response.user;
                
                var modalContent = `
                    <div class="p-4" style="min-height: 400px;">
                        <div class="row g-4">
                            <div class="col-lg-8">
                                <!-- Main Ticket Content -->
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-light border-0 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <i class="ti ti-ticket text-white"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="mb-1 fw-bold text-dark">${support.subject}</h5>
                                                <p class="mb-0 text-muted small">Ticket #${support.id}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-4">
                                            <h6 class="fw-bold text-dark mb-3">
                                                <i class="ti ti-file-text me-2 text-primary"></i>{{ __('Description') }}
                                            </h6>
                                            <div class="bg-light p-3 rounded">
                                                <p class="mb-0">${support.description}</p>
                                            </div>
                                        </div>
                                        
                                        ${support.attachment ? `
                                        <div class="mb-4">
                                            <h6 class="fw-bold text-dark mb-3">
                                                <i class="ti ti-photo me-2 text-primary"></i>{{ __('Attachments') }}
                                            </h6>
                                            <div class="row g-3" id="ticketAttachments">
                                                ${JSON.parse(support.attachment).map(attachment => `
                                                    <div class="col-md-6 col-lg-4">
                                                        <div class="card border-0 shadow-sm h-100">
                                                            <div class="text-center p-4" style="height: 180px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                                                <div class="d-flex flex-column align-items-center">
                                                                    <i class="ti ti-photo text-muted mb-2" style="font-size: 2rem;"></i>
                                                                    <small class="text-muted">{{ __('Click buttons below to view') }}</small>
                                                                </div>
                                                            </div>
                                                            <div class="card-body p-3 text-center">
                                                                <div class="d-flex justify-content-center gap-2">
                                                                    <button type="button" class="btn btn-sm btn-outline-primary view-image-btn" 
                                                                            data-ticket-id="${support.id}" 
                                                                            data-attachment="${attachment}"
                                                                            title="${user.type === 'super admin' || user.type === 'admin' ? '{{__("View Once")}}' : '{{__("View")}}'}">
                                                                        <i class="ti ti-eye me-1"></i>${user.type === 'super admin' || user.type === 'admin' ? '{{__("View Once")}}' : '{{__("View")}}'}
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-outline-secondary preview-image-btn" 
                                                                            data-ticket-id="${support.id}" 
                                                                            data-attachment="${attachment}"
                                                                            title="{{ __('Preview') }}">
                                                                        <i class="ti ti-photo me-1"></i>{{ __('Preview') }}
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `).join('')}
                                            </div>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                                
                                <!-- Discussion Section -->
                                <div class="card border-0 shadow-sm mt-4">
                                    <div class="card-header bg-light border-0 py-3">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="mb-0 fw-bold text-dark">
                                                <i class="ti ti-message-circle me-2 text-primary"></i>{{ __('Discussion') }}
                                            </h6>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge bg-primary rounded-pill">${support.reply.length} {{ __('replies') }}</span>
                                                ${support.reply.length > 3 ? `
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="scrollToBottomBtn" title="{{ __('Scroll to bottom') }}">
                                                    <i class="ti ti-arrow-down"></i>
                                                </button>
                                                ` : ''}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                                        <div id="repliesContainer">
                                            ${support.reply.length > 0 ? support.reply.map((reply, index) => `
                                                <div class="reply-item p-4 ${index !== support.reply.length - 1 ? 'border-bottom' : ''}">
                                                    <div class="d-flex align-items-start">
                                                        <div class="flex-shrink-0">
                                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                                <i class="ti ti-user text-white"></i>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 ms-3">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <div>
                                                                    <h6 class="mb-1 fw-bold text-dark">${reply.user ? reply.user.name : 'Unknown'}</h6>
                                                                    <small class="text-muted">
                                                                        <i class="ti ti-clock me-1"></i>${new Date(reply.created_at).toLocaleString()}
                                                                    </small>
                                                                </div>
                                                                <div class="dropdown">
                                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                                        <i class="ti ti-dots-vertical"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu">
                                                                        <li><a class="dropdown-item" href="#"><i class="ti ti-flag me-2"></i>{{ __('Report') }}</a></li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                            <div class="reply-content">
                                                                <div class="bg-light p-3 rounded-3 mb-3">
                                                                    <p class="mb-0">${reply.description}</p>
                                                                </div>
                                                                ${reply.attachment ? `
                                                                <div class="reply-attachments">
                                                                    <div class="d-flex align-items-center mb-2">
                                                                        <i class="ti ti-paperclip me-2 text-muted"></i>
                                                                        <small class="text-muted fw-medium">{{__('Attachments')}} (${JSON.parse(reply.attachment).length})</small>
                                                                    </div>
                                                                    <div class="row g-2" id="replyAttachments_${reply.id}">
                                                                        ${JSON.parse(reply.attachment).map(attachment => `
                                                                            <div class="col-md-6 col-lg-4">
                                                                                <div class="attachment-card border rounded-3 overflow-hidden shadow-sm">
                                                                                    <div class="text-center p-3" style="height: 100px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                                                                        <div class="d-flex flex-column align-items-center">
                                                                                            <i class="ti ti-photo text-muted mb-1" style="font-size: 1.5rem;"></i>
                                                                                            <small class="text-muted">{{ __('Click to view') }}</small>
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="p-2 text-center bg-light">
                                                                                        <div class="d-flex justify-content-center gap-1">
                                                                                            <button type="button" class="btn btn-sm btn-outline-primary view-reply-image-btn" 
                                                                                                    data-reply-id="${reply.id}" 
                                                                                                    data-attachment="${attachment}"
                                                                                                    title="${user.type === 'super admin' || user.type === 'admin' ? '{{__("View Once")}}' : '{{__("View")}}'}">
                                                                                                <i class="ti ti-eye"></i>
                                                                                            </button>
                                                                                            <button type="button" class="btn btn-sm btn-outline-secondary preview-reply-image-btn" 
                                                                                                    data-reply-id="${reply.id}" 
                                                                                                    data-attachment="${attachment}"
                                                                                                    title="{{ __('Preview') }}">
                                                                                                <i class="ti ti-photo"></i>
                                                                                            </button>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        `).join('')}
                                                                    </div>
                                                                </div>
                                                                ` : ''}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            `).join('') : `
                                                <div class="text-center py-5">
                                                    <div class="empty-state">
                                                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                                            <i class="ti ti-message-circle text-muted" style="font-size: 2rem;"></i>
                                                        </div>
                                                        <h6 class="text-muted mb-2">{{ __('No replies yet') }}</h6>
                                                        <p class="text-muted small mb-0">{{ __('Be the first to add a reply to this ticket') }}</p>
                                                    </div>
                                                </div>
                                            `}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sidebar -->
                            <div class="col-lg-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-header bg-light border-0 py-3">
                                        <h6 class="mb-0 fw-bold text-dark">
                                            <i class="ti ti-info-circle me-2 text-primary"></i>{{ __('Ticket Information') }}
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="mb-4">
                                            <label class="form-label fw-medium text-muted small">{{ __('Status') }}</label>
                                            <div class="mb-2">
                                                <span class="badge bg-${getStatusBadgeClass(support.status)} px-3 py-2 fw-medium">${getStatusText(support.status)}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label fw-medium text-muted small">{{ __('Priority') }}</label>
                                            <div class="mb-2">
                                                <span class="badge bg-${getPriorityBadgeClass(support.priority)} px-3 py-2 fw-medium">${getPriorityText(support.priority)}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label fw-medium text-muted small">{{ __('Created By') }}</label>
                                            <p class="mb-0 fw-bold">${support.created_user ? support.created_user.name : 'Unknown'}</p>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label fw-medium text-muted small">{{ __('Assigned To') }}</label>
                                            <p class="mb-0 fw-bold">${support.assign_user && support.assign_user.name ? support.assign_user.name : '{{ __("Not assigned") }}'}</p>
                                        </div>
                                        
                                        <div class="mb-4">
                                            <label class="form-label fw-medium text-muted small">{{ __('Created Date') }}</label>
                                            <p class="mb-0 fw-bold">${formatDate(support.created_at)}</p>
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        ${canEdit ? `
                                        <div class="mb-3">
                                            <button type="button" class="btn btn-outline-primary w-100" id="editTicketBtn" data-ticket-id="${support.id}">
                                                <i class="ti ti-edit me-2"></i>{{ __('Edit Ticket') }}
                                            </button>
                                        </div>
                                        ` : ''}
                                        
                                        ${canReply ? `
                                        <div class="mb-3">
                                            <button type="button" class="btn btn-primary w-100" id="showReplyBtn">
                                                <i class="ti ti-message-circle me-2"></i>{{ __('Add Reply') }}
                                            </button>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        ${canReply ? `
                        <div class="row mt-4" id="replyFormContainer" style="display: none;">
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-light border-0 py-3">
                                        <h6 class="mb-0 fw-bold text-dark">
                                            <i class="ti ti-message-circle me-2 text-primary"></i>{{ __('Add Reply') }}
                                        </h6>
                                    </div>
                                    <div class="card-body p-4">
                                        <form id="replyForm" enctype="multipart/form-data">
                                            <div class="mb-4">
                                                <label class="form-label fw-medium">{{ __('Reply') }}</label>
                                                <textarea class="form-control" name="description" rows="4" required placeholder="{{ __('Type your reply here...') }}"></textarea>
                                            </div>
                                            <div class="mb-4">
                                                <label class="form-label fw-medium">{{ __('Photo Attachments (Optional)') }}</label>
                                                <input type="file" name="attachment[]" class="form-control" multiple accept="image/*">
                                                <small class="form-text text-muted">{{ __('You can upload multiple photos (JPEG, PNG, JPG, GIF) up to 2MB each') }}</small>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="subm
                                                it" class="btn btn-primary">
                                                    <i class="ti ti-send me-2"></i>{{ __('Send Reply') }}
                                                </button>
                                                <button type="button" class="btn btn-outline-secondary" onclick="hideReplyForm()">
                                                    <i class="ti ti-x me-2"></i>{{ __('Cancel') }}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ` : ''}
                    </div>
                `;
                
                $('#ticketModalContent').html(modalContent);
                
                // Show modal
                $('#ticketModal').modal('show');
                
                // Ensure modal is properly positioned and contained
                setTimeout(function() {
                    $('#ticketModal').modal('handleUpdate');
                }, 100);
                
                // Initialize feather icons
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
                
                // Bind button event handlers
                bindModalButtonHandlers();
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    alert('{{ __("You do not have permission to view this ticket.") }}');
                } else {
                    alert('{{ __("Error loading ticket details.") }}');
                }
            }
        });
    }

    // Handle reply form submission
    $(document).on('submit', '#replyForm', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var ticketId = $('#ticketModal').data('ticket-id');
        
        // Validate form data
        var description = formData.get('description');
        if (!description || description.trim() === '') {
            alert('{{ __("Please enter a reply message.") }}');
            return;
        }
        
        // Show loading state
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.html();
        submitBtn.html('<i class="ti ti-loader ti-spin me-2"></i>{{ __("Sending...") }}').prop('disabled', true);
        
        $.ajax({
            url: '{{ route("support.replyModal", ":id") }}'.replace(':id', ticketId),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showNotification('success', response.message);
                    
                    // Reload the modal content to show the new reply
                    loadTicketModal(ticketId);
                    hideReplyForm();
                    
                    // Scroll to bottom of discussion after reload
                    setTimeout(function() {
                        scrollToBottomOfDiscussion();
                    }, 500);
                } else {
                    showNotification('error', response.message || '{{ __("Error sending reply.") }}');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = '';
                    for (var field in errors) {
                        errorMessage += errors[field].join('\n');
                    }
                    showNotification('error', errorMessage);
                } else if (xhr.status === 403) {
                    showNotification('error', '{{ __("You do not have permission to reply to this ticket.") }}');
                } else {
                    showNotification('error', '{{ __("Error sending reply.") }}');
                }
            },
            complete: function() {
                // Reset button state
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    function showReplyForm() {
        $('#replyFormContainer').show();
        // Focus on the textarea
        setTimeout(function() {
            $('#replyForm textarea[name="description"]').focus();
        }, 100);
    }

    function hideReplyForm() {
        $('#replyFormContainer').hide();
        $('#replyForm')[0].reset();
    }



    function bindModalButtonHandlers() {
        // Bind show reply form button
        $(document).off('click', '#showReplyBtn').on('click', '#showReplyBtn', function() {
            showReplyForm();
        });

        // Bind edit ticket button
        $(document).off('click', '#editTicketBtn').on('click', '#editTicketBtn', function() {
            var ticketId = $(this).data('ticket-id');
            editTicket(ticketId);
        });

        // Bind scroll to bottom button
        $(document).off('click', '#scrollToBottomBtn').on('click', '#scrollToBottomBtn', function() {
            scrollToBottomOfDiscussion();
        });

        // Bind image view buttons
        $(document).off('click', '.view-image-btn').on('click', '.view-image-btn', function() {
            var ticketId = $(this).data('ticket-id');
            var attachment = $(this).data('attachment');
            viewImage(ticketId, attachment);
        });

        // Bind image preview buttons
        $(document).off('click', '.preview-image-btn').on('click', '.preview-image-btn', function() {
            var ticketId = $(this).data('ticket-id');
            var attachment = $(this).data('attachment');
            previewImage(ticketId, attachment);
        });

        // Bind reply image view buttons
        $(document).off('click', '.view-reply-image-btn').on('click', '.view-reply-image-btn', function() {
            var replyId = $(this).data('reply-id');
            var attachment = $(this).data('attachment');
            viewReplyImage(replyId, attachment);
        });

        // Bind reply image preview buttons
        $(document).off('click', '.preview-reply-image-btn').on('click', '.preview-reply-image-btn', function() {
            var replyId = $(this).data('reply-id');
            var attachment = $(this).data('attachment');
            previewReplyImage(replyId, attachment);
        });
    }

    function formatDate(dateString) {
        if (!dateString) {
            return '{{ __("Not available") }}';
        }
        
        try {
            var date = new Date(dateString);
            if (isNaN(date.getTime())) {
                return '{{ __("Invalid date") }}';
            }
            return date.toLocaleString();
        } catch (error) {
            return '{{ __("Date error") }}';
        }
    }

    function scrollToBottomOfDiscussion() {
        var discussionContainer = $('.card-body[style*="overflow-y: auto"]');
        if (discussionContainer.length > 0) {
            discussionContainer.scrollTop(discussionContainer[0].scrollHeight);
        }
    }

    function viewImage(ticketId, attachment) {
        // Show image in preview modal and trigger deletion for admins
        var imageUrl = '{{ url("/storage/public/upload/support/") }}/' + attachment;
        console.log('View Image URL:', imageUrl);
        showImagePreview(imageUrl, attachment, true, ticketId, attachment);
    }

    function previewImage(ticketId, attachment) {
        // Show image preview in modal without triggering deletion
        var imageUrl = '{{ url("/storage/public/upload/support/") }}/' + attachment;
        console.log('Preview Image URL:', imageUrl);
        showImagePreview(imageUrl, attachment, false);
    }   

    function viewReplyImage(replyId, attachment) {
        // Show reply image in preview modal and trigger deletion for admins
        var imageUrl = '{{ url("/storage/public/upload/support/") }}/' + attachment;
        console.log('View Reply Image URL:', imageUrl);
        showImagePreview(imageUrl, attachment, true, null, null, replyId, attachment);
    }

    function previewReplyImage(replyId, attachment) {
        // Show reply image preview in modal without triggering deletion
        var imageUrl = '{{ url("/storage/public/upload/support/") }}/' + attachment;
        console.log('Preview Reply Image URL:', imageUrl);
        showImagePreview(imageUrl, attachment, false);
    }

    function handleViewOnceImageLoad() {
        // Handle image deletion for view once functionality
        var viewOnceData = $('#imagePreviewModal').data('view-once');
        if (viewOnceData) {
            // Trigger deletion on server
            if (viewOnceData.ticketId && viewOnceData.attachment) {
                // Delete ticket attachment
                $.get('{{ url("/support/") }}/' + viewOnceData.ticketId + '/photo/' + viewOnceData.attachment, function() {
                    console.log('Ticket image deleted');
                });
            } else if (viewOnceData.replyId && viewOnceData.replyAttachment) {
                // Delete reply attachment
                $.get('{{ url("/support/reply/") }}/' + viewOnceData.replyId + '/photo/' + viewOnceData.replyAttachment, function() {
                    console.log('Reply image deleted');
                });
            }
        }
    }

    function showImagePreview(imageUrl, filename, isViewOnce = false, ticketId = null, attachment = null, replyId = null, replyAttachment = null) {
        console.log('showImagePreview called with:', {
            imageUrl: imageUrl,
            filename: filename,
            isViewOnce: isViewOnce,
            ticketId: ticketId,
            
             attachment,
            replyId: replyId,
            replyAttachment: replyAttachment
        });
        
        // Create and show image preview modal
        var modalTitle = isViewOnce ? '{{ __("View Once") }}' : '{{ __("Image Preview") }}';
        var modalIcon = isViewOnce ? 'ti-eye' : 'ti-photo';
        var modalClass = isViewOnce ? 'bg-warning' : 'bg-light';
        var titleClass = isViewOnce ? 'text-white' : 'text-dark';
        
        var previewModal = `
            <div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header ${modalClass} border-0 py-3">
                            <h6 class="modal-title fw-bold ${titleClass}">
                                <i class="ti ${modalIcon} me-2 ${isViewOnce ? 'text-white' : 'text-primary'}"></i>${modalTitle}
                            </h6>
                            <button type="button" class="btn-close ${isViewOnce ? 'btn-close-white' : ''}" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0 text-center">
                            <div class="p-4">
                                <div class="text-center mb-3">
                                    <small class="text-muted">Loading image from: ${imageUrl}</small>
                                </div>
                                <img src="${imageUrl}" class="img-fluid rounded shadow-sm" alt="${filename}" 
                                     style="max-height: 70vh; object-fit: contain;"
                                     onload="console.log('Image loaded successfully: ' + this.src); ${isViewOnce ? 'handleViewOnceImageLoad()' : ''}"
                                     onerror="console.log('Image failed to load: ' + this.src); this.parentElement.innerHTML='<div class=\'text-center py-5 text-muted\'><i class=\'ti ti-photo-off\' style=\'font-size: 3rem;\'></i><br><p class=\'mt-3\'>{{ __("Image not available") }}</p><small class=\'text-muted\'>URL: ${imageUrl}</small></div>';">
                            </div>
                        </div>
                        <div class="modal-footer ${modalClass} border-0 py-2">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <small class="${isViewOnce ? 'text-white' : 'text-muted'}">${filename}</small>
                                <button type="button" class="btn btn-sm ${isViewOnce ? 'btn-outline-light' : 'btn-outline-secondary'}" data-bs-dismiss="modal">
                                    <i class="ti ti-x me-1"></i>{{ __('Close') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing preview modal if any
        $('#imagePreviewModal').remove();
        
        // Add new preview modal to body
        $('body').append(previewModal);
        
        // Show the modal
        $('#imagePreviewModal').modal('show');
        
        // Store view once data for deletion
        if (isViewOnce) {
            $('#imagePreviewModal').data('view-once', {
                ticketId: ticketId,
                attachment: attachment,
                replyId: replyId,
                replyAttachment: replyAttachment
            });
        }
        
        // Remove modal from DOM when hidden
        $('#imagePreviewModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    }

    function editTicket(ticketId) {
        // Open edit modal
        var editUrl = '{{ route("support.edit", ":id") }}'.replace(':id', ticketId);
        $('.customModal[data-url="' + editUrl + '"]').click();
    }

    // Notification function
    function showNotification(type, message) {
        // Check if notification library is available
        if (typeof $.notify !== 'undefined') {
            $.notify({
                message: message
            }, {
                type: type,
                placement: {
                    from: 'top',
                    align: 'right'
                },
                delay: 3000,
                animate: {
                    enter: 'animated fadeInDown',
                    exit: 'animated fadeOutUp'
                }
            });
        } else {
            // Fallback to alert
            alert(message);
        }
    }

    function getStatusBadgeClass(status) {
        switch(status) {
            case 'pending': return 'primary';
            case 'open': return 'info';
            case 'close': return 'danger';
            case 'on_hold': return 'warning';
            default: return 'secondary';
        }
    }

    function getStatusText(status) {
        switch(status) {
            case 'pending': return '{{ __("Pending") }}';
            case 'open': return '{{ __("Open") }}';
            case 'close': return '{{ __("Close") }}';
            case 'on_hold': return '{{ __("On Hold") }}';
            default: return status;
        }
    }

    function getPriorityBadgeClass(priority) {
        switch(priority) {
            case 'low': return 'success';
            case 'medium': return 'info';
            case 'high': return 'warning';
            case 'critical': return 'danger';
            default: return 'secondary';
        }
    }

    function getPriorityText(priority) {
        switch(priority) {
            case 'low': return '{{ __("Low") }}';
            case 'medium': return '{{ __("Medium") }}';
            case 'high': return '{{ __("High") }}';
            case 'critical': return '{{ __("Critical") }}';
            default: return priority;
        }
    }
 });
 }
 
     // Start initialization
    initTicketModal();
    
    // Make functions globally accessible
    window.showReplyForm = showReplyForm;
    window.hideReplyForm = hideReplyForm;
    window.editTicket = editTicket;
    window.bindModalButtonHandlers = bindModalButtonHandlers;
    window.formatDate = formatDate;
    window.scrollToBottomOfDiscussion = scrollToBottomOfDiscussion;
    window.viewImage = viewImage;
    window.previewImage = previewImage;
    window.viewReplyImage = viewReplyImage;
    window.previewReplyImage = previewReplyImage;
    window.showImagePreview = showImagePreview;
    window.handleViewOnceImageLoad = handleViewOnceImageLoad;
  </script> 