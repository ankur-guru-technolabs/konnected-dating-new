<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\LoginController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [LoginController::class, 'showLoginForm'])->name('/');
Route::post('/login-admin', [LoginController::class, 'login'])->name('login-admin');
Route::get('privacy-policy', [LoginController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('terms-condition', [LoginController::class, 'termsCondition'])->name('terms-condition');
Route::get('contact', [LoginController::class, 'contactForm'])->name('contact');
Route::post('contact-us-store', [LoginController::class, 'contactStore'])->name('contact-us-store');

// FOR GOOGLE CURRENTLY THIS IS NOT USED

Route::get('authorized/{provider}', [AuthController::class, 'redirectToProvider']);
Route::get('authorized/{provider}/callback', [AuthController::class, 'handleProviderCallback']); 


Route::middleware(['admin'])->group(function () {

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::group(['prefix' => 'questions','as'=>'questions.'], function () {
        Route::group(['prefix' => 'ages','as'=>'ages.'], function () {
            Route::get('list', [AdminController::class, 'ageList'])->name('list');
            Route::post('store', [AdminController::class, 'ageStore'])->name('store');
            Route::post('update', [AdminController::class, 'ageUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'ageDelete'])->name('delete');
        });
        Route::group(['prefix' => 'bodytype','as'=>'bodytype.'], function () {
            Route::get('list', [AdminController::class, 'bodytypeList'])->name('list');
            Route::post('store', [AdminController::class, 'bodytypeStore'])->name('store');
            Route::post('update', [AdminController::class, 'bodytypeUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'bodytypeDelete'])->name('delete');
        });
        Route::group(['prefix' => 'children','as'=>'children.'], function () {
            Route::get('list', [AdminController::class, 'childrenList'])->name('list');
            Route::post('store', [AdminController::class, 'childrenStore'])->name('store');
            Route::post('update', [AdminController::class, 'childrenUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'childrenDelete'])->name('delete');
        });
        Route::group(['prefix' => 'education','as'=>'education.'], function () {
            Route::get('list', [AdminController::class, 'educationList'])->name('list');
            Route::post('store', [AdminController::class, 'educationStore'])->name('store');
            Route::post('update', [AdminController::class, 'educationUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'educationDelete'])->name('delete');
        });
        Route::group(['prefix' => 'ethnicity','as'=>'ethnicity.'], function () {
            Route::get('list', [AdminController::class, 'ethnicityList'])->name('list');
            Route::post('store', [AdminController::class, 'ethnicityStore'])->name('store');
            Route::post('update', [AdminController::class, 'ethnicityUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'ethnicityDelete'])->name('delete');
        });
        Route::group(['prefix' => 'faith','as'=>'faith.'], function () {
            Route::get('list', [AdminController::class, 'faithList'])->name('list');
            Route::post('store', [AdminController::class, 'faithStore'])->name('store');
            Route::post('update', [AdminController::class, 'faithUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'faithDelete'])->name('delete');
        });
        Route::group(['prefix' => 'gender','as'=>'gender.'], function () {
            Route::get('list', [AdminController::class, 'genderList'])->name('list');
            Route::post('store', [AdminController::class, 'genderStore'])->name('store');
            Route::post('update', [AdminController::class, 'genderUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'genderDelete'])->name('delete');
        });
        Route::group(['prefix' => 'height','as'=>'height.'], function () {
            Route::get('list', [AdminController::class, 'heightList'])->name('list');
            Route::post('store', [AdminController::class, 'heightStore'])->name('store');
            Route::post('update', [AdminController::class, 'heightUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'heightDelete'])->name('delete');
        });
        Route::group(['prefix' => 'hobby','as'=>'hobby.'], function () {
            Route::get('list', [AdminController::class, 'hobbyList'])->name('list');
            Route::post('store', [AdminController::class, 'hobbyStore'])->name('store');
            Route::post('update', [AdminController::class, 'hobbyUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'hobbyDelete'])->name('delete');
        });
        Route::group(['prefix' => 'icebreaker','as'=>'icebreaker.'], function () {
            Route::get('list', [AdminController::class, 'icebreakerList'])->name('list');
            Route::post('store', [AdminController::class, 'icebreakerStore'])->name('store');
            Route::post('update', [AdminController::class, 'icebreakerUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'icebreakerDelete'])->name('delete');
        });
        Route::group(['prefix' => 'industry','as'=>'industry.'], function () {
            Route::get('list', [AdminController::class, 'industryList'])->name('list');
            Route::post('store', [AdminController::class, 'industryStore'])->name('store');
            Route::post('update', [AdminController::class, 'industryUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'industryDelete'])->name('delete');
        });
        Route::group(['prefix' => 'question','as'=>'question.'], function () {
            Route::get('list', [AdminController::class, 'questionList'])->name('list');
            Route::post('store', [AdminController::class, 'questionStore'])->name('store');
            Route::post('update', [AdminController::class, 'questionUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'questionDelete'])->name('delete');
            Route::get('subQuestion/{id}', [AdminController::class, 'subQuestionList'])->name('subQuestionList');
        });
        Route::group(['prefix' => 'salary','as'=>'salary.'], function () {
            Route::get('list', [AdminController::class, 'salaryList'])->name('list');
            Route::post('store', [AdminController::class, 'salaryStore'])->name('store');
            Route::post('update', [AdminController::class, 'salaryUpdate'])->name('update');
            Route::get('delete/{id}', [AdminController::class, 'salaryDelete'])->name('delete');
        });
    });
    
    Route::group(['prefix' => 'users','as'=>'users.'], function () {
        Route::get('list', [UserController::class, 'list'])->name('list');
        Route::post('status/update', [UserController::class, 'updateStatus'])->name('status-update');
    });

    Route::group(['prefix' => 'feedback','as'=>'feedback.'], function () {
        Route::get('list', [AdminController::class, 'feedbackList'])->name('list');
    });

    Route::group(['prefix' => 'static-pages','as'=>'static-pages.'], function () {
        Route::get('list', [AdminController::class, 'staticPagesList'])->name('list');
        Route::get('page-edit/{id}', [AdminController::class, 'pageEdit'])->name('page-edit');
        Route::post('page-update', [AdminController::class, 'pageUpdate'])->name('page-update');
    });
  
    Route::group(['prefix' => 'subscription','as'=>'subscription.'], function () {
        Route::get('order', [AdminController::class, 'subscriptionOrder'])->name('order');
        Route::get('list', [AdminController::class, 'subscriptionList'])->name('list');
        Route::get('subscription-edit/{id}', [AdminController::class, 'subscriptionEdit'])->name('subscription-edit');
        Route::post('subscription-update', [AdminController::class, 'subscriptionUpdate'])->name('subscription-update');
    });
    
    Route::group(['prefix' => 'category','as'=>'category.'], function () {
        Route::get('list', [AdminController::class, 'categoryList'])->name('list');
        Route::post('store', [AdminController::class, 'categoryStore'])->name('store');
        Route::post('update', [AdminController::class, 'categoryUpdate'])->name('update');
        Route::get('delete/{id}', [AdminController::class, 'categoryDelete'])->name('delete');
    });
   
    Route::group(['prefix' => 'faq','as'=>'faq.'], function () {
        Route::get('list', [AdminController::class, 'faqList'])->name('list');
        Route::get('add', [AdminController::class, 'faqAdd'])->name('faq-add');
        Route::post('store', [AdminController::class, 'faqStore'])->name('faq-store');
        Route::get('faq-edit/{id}', [AdminController::class, 'faqEdit'])->name('faq-edit');
        Route::post('faq-update', [AdminController::class, 'faqUpdate'])->name('faq-update');
        Route::get('faq-delete/{id}', [AdminController::class, 'faqDelete'])->name('faq-delete');
    });

    Route::group(['prefix' => 'coin','as'=>'coin.'], function () {
        Route::get('list', [AdminController::class, 'coinList'])->name('list');
        Route::post('store', [AdminController::class, 'coinStore'])->name('store');
        Route::post('update', [AdminController::class, 'coinUpdate'])->name('update');
        Route::get('delete/{id}', [AdminController::class, 'coinDelete'])->name('delete');
    });
   
    Route::group(['prefix' => 'gift','as'=>'gift.'], function () {
        Route::get('list', [AdminController::class, 'giftList'])->name('list');
        Route::post('store', [AdminController::class, 'giftStore'])->name('store');
        Route::post('update', [AdminController::class, 'giftUpdate'])->name('update');
        Route::get('delete/{id}', [AdminController::class, 'giftDelete'])->name('delete');
    });
    
    Route::group(['prefix' => 'notification','as'=>'notification.'], function () {
        Route::get('index', [AdminController::class, 'notificationIndex'])->name('index');
        Route::post('send', [AdminController::class, 'notificationSend'])->name('send');
    });

    Route::group(['prefix' => 'report','as'=>'report.'], function () {
        Route::get('list', [AdminController::class, 'reportList'])->name('list');
        Route::post('user-block', [AdminController::class, 'userBlock'])->name('user-block');
    });
});

Route::get('/subscription-expire', [LoginController::class, 'subscriptionExpire'])->name('cron');
Route::get('/message-delete', [LoginController::class, 'messageDelete'])->name('cron');

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
// Auth::routes();

Route::post('/logout', function () {
    Auth::logout();

    Session::forget('session_start_time');
    Session::forget('session_lifetime');
    
    return redirect('/');
})->name('logout');
