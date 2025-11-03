@extends('layouts.app')
@section('page-title')
    {{__('Ticket Details')}}
@endsection

@section('breadcrumb')
    <ul class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{route('dashboard')}}">{{__('Dashboard')}}</a>
        </li>
        <li class="breadcrumb-item">
            <a href="{{route('support.index')}}">{{__('Tickets')}}</a>
        </li>
        <li class="breadcrumb-item active">
            <a href="#">{{__('Details')}}</a>
        </li>
    </ul>
@endsection
@section('content')
    <div class="row g-4">
        <!-- Main Ticket Content -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light border-0 py-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="ti ti-ticket text-white fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h4 class="mb-1 fw-bold text-dark">{{$support->subject}}</h4>
                                <p class="mb-0 text-muted">Ticket #{{$support->id}}</p>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            @if($support->priority=='low')
                                <span class="badge bg-success px-3 py-2 fw-medium">{{\App\Models\Support::$priority[$support->priority]}}</span>
                            @elseif($support->priority=='medium')
                                <span class="badge bg-info px-3 py-2 fw-medium">{{\App\Models\Support::$priority[$support->priority]}}</span>
                            @elseif($support->priority=='high')
                                <span class="badge bg-warning px-3 py-2 fw-medium">{{\App\Models\Support::$priority[$support->priority]}}</span>
                            @elseif($support->priority=='critical')
                                <span class="badge bg-danger px-3 py-2 fw-medium">{{\App\Models\Support::$priority[$support->priority]}}</span>
                            @endif
                            
                            @if($support->status=='pending')
                                <span class="badge bg-primary px-3 py-2 fw-medium">{{\App\Models\Support::$status[$support->status]}}</span>
                            @elseif($support->status=='open')
                                <span class="badge bg-info px-3 py-2 fw-medium">{{\App\Models\Support::$status[$support->status]}}</span>
                            @elseif($support->status=='close')
                                <span class="badge bg-danger px-3 py-2 fw-medium">{{\App\Models\Support::$status[$support->status]}}</span>
                            @elseif($support->status=='on_hold')
                                <span class="badge bg-warning px-3 py-2 fw-medium">{{\App\Models\Support::$status[$support->status]}}</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-3">
                            <i class="ti ti-file-text me-2 text-primary"></i>{{ __('Description') }}
                        </h6>
                        <div class="bg-light p-4 rounded-3">
                            <p class="mb-0">{{$support->description}}</p>
                        </div>
                    </div>
                    
                    @if($support->attachment)
                        <div class="mb-4">
                            <h6 class="fw-bold text-dark mb-3">
                                <i class="ti ti-photo me-2 text-primary"></i>{{ __('Attachments') }}
                            </h6>
                            <div class="row g-3">
                                @php
                                    $attachments = json_decode($support->attachment, true);
                                @endphp
                                @if(is_array($attachments))
                                    @foreach($attachments as $attachment)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card border-0 shadow-sm h-100">
                                                <div class="text-center p-4" style="height: 200px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <i class="ti ti-photo text-muted mb-2" style="font-size: 2rem;"></i>
                                                        <small class="text-muted">{{ __('Click to view') }}</small>
                                                    </div>
                                                </div>
                                                <div class="card-body p-3 text-center">
                                                    <a href="{{ route('support.viewPhoto', ['ticketId' => $support->id, 'photoName' => $attachment]) }}" 
                                                       target="_blank" 
                                                       class="btn btn-sm btn-outline-primary"
                                                       onclick="return confirm('{{ __('This photo will be permanently deleted after viewing. Continue?') }}')">
                                                        <i class="ti ti-eye me-1"></i> {{__('View Once')}}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endif
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
                        <label class="form-label fw-medium text-muted small">{{ __('Created By') }}</label>
                        <p class="mb-0 fw-bold">{{ !empty($support->createdUser)?$support->createdUser->name:'-' }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-medium text-muted small">{{ __('Assigned To') }}</label>
                        <p class="mb-0 fw-bold">{{ !empty($support->assignUser)?$support->assignUser->name:__('Not assigned') }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-medium text-muted small">{{ __('Created Date') }}</label>
                        <p class="mb-0 fw-bold">{{dateFormat($support->created_at)}}</p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label fw-medium text-muted small">{{ __('Last Updated') }}</label>
                        <p class="mb-0 fw-bold">{{dateFormat($support->updated_at)}}</p>
                    </div>
                    
                    <hr class="my-4">
                    
                    @php
                        $canReply = (\Auth::user()->type == 'super admin' || \Auth::user()->type == 'admin' || $support->created_id == \Auth::user()->id || $support->assign_user == \Auth::user()->id);
                        $canClose = (\Auth::user()->type == 'super admin' || \Auth::user()->type == 'admin');
                    @endphp
                    
                    @if(($support->status == 'open' || $support->status == 'pending') && $canReply)
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#replyModal">
                                <i class="ti ti-message-circle me-2"></i>{{ __('Add Reply') }}
                            </button>
                        </div>
                    @endif
                    
                    @if($canClose && ($support->status == 'open' || $support->status == 'pending'))
                        <div class="mb-3">
                            <a href="{{ route('support.close', $support->id) }}" class="btn btn-outline-danger w-100" onclick="return confirm('{{ __('Are you sure you want to close this ticket?') }}')">
                                <i class="ti ti-x-circle me-2"></i>{{ __('Close Ticket') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Discussion Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-0 py-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="mb-0 fw-bold text-dark">
                            <i class="ti ti-message-circle me-2 text-primary"></i>{{ __('Discussion') }}
                        </h6>
                        <span class="badge bg-primary rounded-pill">{{$support->reply->count()}} {{ __('replies') }}</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($support->reply->count() > 0)
                        <div class="p-4">
                            @foreach($support->reply as $reply)
                                <div class="reply-item p-4 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-shrink-0">
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="ti ti-user text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h6 class="mb-1 fw-bold text-dark">{{!empty($reply->user)?$reply->user->name:''}}</h6>
                                                    <small class="text-muted">
                                                        <i class="ti ti-clock me-1"></i>{{dateFormat($reply->created_at)}}
                                                    </small>
                                                </div>
                                                @if($reply->user_id == \Auth::user()->id)
                                                    <span class="badge bg-success">{{ __('You') }}</span>
                                                @endif
                                            </div>
                                            <div class="reply-content">
                                                <div class="bg-light p-3 rounded-3 mb-3">
                                                    <p class="mb-0">{{$reply->description}}</p>
                                                </div>
                                                
                                                @if($reply->attachment)
                                                    <div class="reply-attachments">
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="ti ti-paperclip me-2 text-muted"></i>
                                                            <small class="text-muted fw-medium">{{__('Attachments')}} ({{count(json_decode($reply->attachment, true))}})</small>
                                                        </div>
                                                        <div class="row g-2">
                                                            @php
                                                                $replyAttachments = json_decode($reply->attachment, true);
                                                            @endphp
                                                            @if(is_array($replyAttachments))
                                                                @foreach($replyAttachments as $attachment)
                                                                    <div class="col-md-6 col-lg-4">
                                                                        <div class="attachment-card border rounded-3 overflow-hidden shadow-sm">
                                                                            <div class="text-center p-3" style="height: 100px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
                                                                                <div class="d-flex flex-column align-items-center">
                                                                                    <i class="ti ti-photo text-muted mb-1" style="font-size: 1.5rem;"></i>
                                                                                    <small class="text-muted">{{ __('Click to view') }}</small>
                                                                                </div>
                                                                            </div>
                                                                            <div class="p-2 text-center bg-light">
                                                                                <a href="{{ route('support.viewReplyPhoto', ['replyId' => $reply->id, 'photoName' => $attachment]) }}" 
                                                                                   target="_blank" 
                                                                                   class="btn btn-sm btn-outline-primary"
                                                                                   onclick="return confirm('{{ __('This photo will be permanently deleted after viewing. Continue?') }}')">
                                                                                    <i class="ti ti-eye"></i> {{__('View Once')}}
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="empty-state">
                                <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                                    <i class="ti ti-message-circle text-muted" style="font-size: 2rem;"></i>
                                </div>
                                <h6 class="text-muted mb-2">{{ __('No replies yet') }}</h6>
                                <p class="text-muted small mb-0">{{ __('Be the first to add a reply to this ticket') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Reply Modal -->
    @if(($support->status == 'open' || $support->status == 'pending') && $canReply)
        <div class="modal fade" id="replyModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white border-0 py-3">
                        <h5 class="modal-title fw-bold">
                            <i class="ti ti-message-circle me-2"></i>{{ __('Add Reply') }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        {{Form::open(array('route'=>array('support.reply',$support->id),'method'=>'post', 'enctype' => "multipart/form-data"))}}
                            <div class="mb-4">
                                <label class="form-label fw-medium">{{ __('Reply') }}</label>
                                <textarea class="form-control" rows="4" name="description" placeholder="{{__('Write your reply...')}}" required></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-medium">{{ __('Photo Attachments (Optional)') }}</label>
                                <input type="file" name="attachment[]" class="form-control" multiple accept="image/*">
                                <small class="form-text text-muted">{{ __('You can upload multiple photos (JPEG, PNG, JPG, GIF) up to 2MB each') }}</small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-send me-2"></i>{{ __('Send Reply') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                    <i class="ti ti-x me-2"></i>{{ __('Cancel') }}
                                </button>
                            </div>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

<style>
.reply-item:hover {
    background-color: #f8f9fa;
    transition: background-color 0.2s ease;
}

.empty-state {
    min-height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.attachment-card {
    transition: transform 0.2s ease;
}

.attachment-card:hover {
    transform: translateY(-2px);
}

.card {
    transition: box-shadow 0.2s ease;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>

