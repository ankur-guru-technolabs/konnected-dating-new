<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3   bg-gradient-dark" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{route('/')}}">
            <img src="{{ asset('images/konnected-dating-red.png') }}" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold text-white">Konnected Dating</span>
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    @php

        function isActivePrefix($routeName,$className) {
            if (Str::startsWith(Route::getCurrentRoute()->getPrefix(), 'questions/') && $routeName == "questions") {
                return $className;
            }
            return trim(Route::getCurrentRoute()->getPrefix(), '/') == $routeName ? $className : '';
        }

        function isActive($routeName) {
            return Route::currentRouteName() == $routeName ? 'active' : '';
        }

    @endphp
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link text-white  {{ (Route::currentRouteName() == 'dashboard') ? 'active bg-gradient-primary' : '' }}" href="{{route('dashboard')}}">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">dashboard</i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#question_management_menu" class="nav-link text-white {{ isActivePrefix('questions','active') }}" aria-controls="question_management_menu" role="button" aria-expanded="{{ isActivePrefix('questions','true') }}">
                    <i class="material-icons-round opacity-10">quiz</i>
                    <span class="nav-link-text ms-2 ps-1">Question Handling</span>
                </a>
                <div class="collapse {{ isActivePrefix('questions','show') }}" id="question_management_menu">
                    <ul class="nav ">
                        <li class="nav-item {{isActive('questions.ages.list')}}">
                            <a class="nav-link text-white {{isActive('questions.ages.list')}}" href="{{route('questions.ages.list')}}">
                                <img class="menu-img-class" src="{{ asset('images/age.png') }}">
                                <span class="sidenav-normal  ms-2  ps-1">Age</span>
                            </a>
                        </li>
                        <li class="nav-item {{isActive('questions.bodytype.list')}}">
                            <a class="nav-link text-white {{isActive('questions.bodytype.list')}}" href="{{route('questions.bodytype.list')}}">
                                <img class="menu-img-class" src="{{ asset('images/body-type.png') }}">
                                <span class="sidenav-normal  ms-2  ps-1">Body Types</span>
                            </a>
                        </li>
                        <li class="nav-item {{isActive('questions.children.list')}}">
                            <a class="nav-link text-white {{isActive('questions.children.list')}}" href="{{route('questions.children.list')}}">
                                <img class="menu-img-class" src="{{ asset('images/children.png') }}">
                                <span class="sidenav-normal  ms-2  ps-1">Childrens</span>
                            </a>
                        </li>
                        <li class="nav-item {{isActive('questions.education.list')}}">
                            <a class="nav-link text-white {{isActive('questions.education.list')}}" href="{{route('questions.education.list')}}">
                                <img class="menu-img-class" src="{{ asset('images/education.png') }}">
                                <span class="sidenav-normal  ms-2  ps-1">Education</span>
                            </a>
                        </li>
                        <li class="nav-item {{isActive('questions.ethnicity.list')}}">
                            <a class="nav-link text-white {{isActive('questions.ethnicity.list')}}" href="{{route('questions.ethnicity.list')}}">
                                <img class="menu-img-class" src="{{ asset('images/ethincities.png') }}">
                                <span class="sidenav-normal  ms-2  ps-1">Ethnicities</span>
                            </a>
                        </li>
                        <li class="nav-item {{isActive('questions.faith.list')}}">
                            <a class="nav-link text-white {{isActive('questions.faith.list')}}" href="{{route('questions.faith.list')}}">
                                <img class="menu-img-class" src="{{ asset('images/faith.png') }}">
                                <span class="sidenav-normal  ms-2  ps-1">Faiths</span>
                            </a>
                        </li>
                        <li class="nav-item {{isActive('questions.gender.list')}}">
                            <a class="nav-link text-white {{isActive('questions.gender.list')}}" href="{{route('questions.gender.list')}}">
                                <img class="menu-img-class" src="{{ asset('images/genders.png') }}">
                                <span class="sidenav-normal  ms-2  ps-1">Genders</span>
                            </a>
                        </li>
                        <li class="nav-item {{isActive('questions.height.list')}}">
                            <a class="nav-link text-white {{isActive('questions.height.list')}}" href="{{route('questions.height.list')}}">
                                <img class="menu-img-class" src="{{ asset('images/height.png') }}">
                                <span class="sidenav-normal  ms-2  ps-1">Heights</span>
                            </a>
                        </li>
                        <li class="nav-item {{isActive('questions.hobby.list')}}">
                            <a class="nav-link text-white {{isActive('questions.hobby.list')}}" href="{{route('questions.hobby.list')}}">
                                <img class="menu-img-class" src="{{ asset('images/hobbies.png') }}">
                                <span class="sidenav-normal  ms-2  ps-1">Hobbies</span>
                            </a>
                        </li>
                        <li class="nav-item {{isActive('questions.icebreaker.list')}}">
                            <a class="nav-link text-white {{isActive('questions.icebreaker.list')}}" href="{{route('questions.icebreaker.list')}}">
                                <img class="menu-img-class" src="{{ asset('images/ice-breaker.png') }}">
                                <span class="sidenav-normal  ms-2  ps-1">Ice Breakers</span>
                            </a>
                        </li>
                        <li class="nav-item {{isActive('questions.industry.list')}}">
                            <a class="nav-link text-white {{isActive('questions.industry.list')}}" href="{{route('questions.industry.list')}}">
                                <img class="menu-img-class" src="{{ asset('images/industry.png') }}">
                                <span class="sidenav-normal  ms-2  ps-1">Industries</span>
                            </a>
                        </li>
                        <li class="nav-item {{isActive('questions.question.list')}}">
                            <a class="nav-link text-white {{isActive('questions.question.list')}}" href="{{route('questions.question.list')}}">
                                <img class="menu-img-class" src="{{ asset('images/question.png') }}">
                                <span class="sidenav-normal  ms-2  ps-1">Questions</span>
                            </a>
                        </li>
                        <li class="nav-item {{isActive('questions.salary.list')}}">
                            <a class="nav-link text-white {{isActive('questions.salary.list')}}" href="{{route('questions.salary.list')}}">
                                <img class="menu-img-class" src="{{ asset('images/salary.png') }}">
                                <span class="sidenav-normal  ms-2  ps-1">Salaries</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#users_menu" class="nav-link text-white {{ isActivePrefix('users','active') }}" aria-controls="users_menu" role="button" aria-expanded="{{ isActivePrefix('users','true') }}">
                    <i class="material-icons-round opacity-10">group</i>
                    <span class="nav-link-text ms-2 ps-1">Users</span>
                </a>
                <div class="collapse {{ isActivePrefix('users','show') }}" id="users_menu">
                    <ul class="nav ">
                        <!-- <li class="nav-item {{ isActive('users.list') }}">
                            <a class="nav-link text-white {{ isActive('users.list') }}" href="{{route('users.list')}}">
                                <i class="material-icons opacity-10">person_add</i>
                                <span class="sidenav-normal  ms-2  ps-1">Add User</span>
                            </a>
                        </li> -->
                        <li class="nav-item {{isActive('users.list')}}">
                            <a class="nav-link text-white {{isActive('users.list')}}" href="{{route('users.list')}}">
                                <i class="material-icons opacity-10">list_alt</i>
                                <span class="sidenav-normal  ms-2  ps-1">List</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#feedback_menu" class="nav-link text-white {{ isActivePrefix('feedback','active') }}" aria-controls="feedback_menu" role="button" aria-expanded="{{ isActivePrefix('feedback','true') }}">
                    <i class="material-icons-round opacity-10">chat_bubble</i>
                    <span class="nav-link-text ms-2 ps-1">Feedback</span>
                </a>
                <div class="collapse {{ isActivePrefix('feedback','show') }}" id="feedback_menu">
                    <ul class="nav ">
                        <li class="nav-item {{isActive('feedback.list')}}">
                            <a class="nav-link text-white {{isActive('feedback.list')}}" href="{{route('feedback.list')}}">
                                <i class="material-icons opacity-10">list_alt</i>
                                <span class="sidenav-normal  ms-2  ps-1">List</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#static_menu" class="nav-link text-white {{ isActivePrefix('static-pages','active') }}" aria-controls="static_menu" role="button" aria-expanded="{{ isActivePrefix('static-pages','true') }}">
                    <i class="material-icons-round opacity-10">description</i>
                    <span class="nav-link-text ms-2 ps-1">Static Pages</span>
                </a>
                <div class="collapse {{ isActivePrefix('static-pages','show') }}" id="static_menu">
                    <ul class="nav ">
                        <li class="nav-item {{isActive('static-pages.list')}}">
                            <a class="nav-link text-white {{isActive('static-pages.list')}}" href="{{route('static-pages.list')}}">
                                <i class="material-icons opacity-10">list_alt</i>
                                <span class="sidenav-normal  ms-2  ps-1">List</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#subscription_menu" class="nav-link text-white {{ isActivePrefix('subscription','active') }}" aria-controls="subscription_menu" role="button" aria-expanded="{{ isActivePrefix('subscription','true') }}">
                    <i class="material-icons-round opacity-10">subscriptions</i>
                    <span class="nav-link-text ms-2 ps-1">Subscription</span>
                </a>
                <div class="collapse {{ isActivePrefix('subscription','show') }}" id="subscription_menu">
                    <ul class="nav ">
                        <li class="nav-item {{isActive('subscription.list')}}">
                            <a class="nav-link text-white {{isActive('subscription.list')}}" href="{{route('subscription.list')}}">
                                <i class="material-icons opacity-10">list_alt</i>
                                <span class="sidenav-normal  ms-2  ps-1">List</span>
                            </a>
                        </li>
                        <li class="nav-item {{isActive('subscription.order')}}">
                            <a class="nav-link text-white {{isActive('subscription.order')}}" href="{{route('subscription.order')}}">
                                <i class="material-icons opacity-10">list_alt</i>
                                <span class="sidenav-normal  ms-2  ps-1">Order</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#category_menu" class="nav-link text-white {{ isActivePrefix('category','active') }}" aria-controls="category_menu" role="button" aria-expanded="{{ isActivePrefix('category','true') }}">
                    <i class="material-icons-round opacity-10">category</i>
                    <span class="nav-link-text ms-2 ps-1">Category</span>
                </a>
                <div class="collapse {{ isActivePrefix('category','show') }}" id="category_menu">
                    <ul class="nav ">
                        <li class="nav-item {{isActive('category.list')}}">
                            <a class="nav-link text-white {{isActive('category.list')}}" href="{{route('category.list')}}">
                                <i class="material-icons opacity-10">list_alt</i>
                                <span class="sidenav-normal  ms-2  ps-1">List</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#faq_menu" class="nav-link text-white {{ isActivePrefix('faq','active') }}" aria-controls="faq_menu" role="button" aria-expanded="{{ isActivePrefix('faq','true') }}">
                    <i class="material-icons-round opacity-10">quiz</i>
                    <span class="nav-link-text ms-2 ps-1">FAQ</span>
                </a>
                <div class="collapse {{ isActivePrefix('faq','show') }}" id="faq_menu">
                    <ul class="nav ">
                        <li class="nav-item {{isActive('faq.list')}}">
                            <a class="nav-link text-white {{isActive('faq.list')}}" href="{{route('faq.list')}}">
                                <i class="material-icons opacity-10">list_alt</i>
                                <span class="sidenav-normal  ms-2  ps-1">List</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#coin_menu" class="nav-link text-white {{ isActivePrefix('coin','active') }}" aria-controls="coin_menu" role="button" aria-expanded="{{ isActivePrefix('coin','true') }}">
                    <i class="material-icons-round opacity-10">monetization_on</i>
                    <span class="nav-link-text ms-2 ps-1">Coin</span>
                </a>
                <div class="collapse {{ isActivePrefix('coin','show') }}" id="coin_menu">
                    <ul class="nav ">
                        <li class="nav-item {{isActive('coin.list')}}">
                            <a class="nav-link text-white {{isActive('coin.list')}}" href="{{route('coin.list')}}">
                                <i class="material-icons opacity-10">list_alt</i>
                                <span class="sidenav-normal  ms-2  ps-1">List</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#gift_menu" class="nav-link text-white {{ isActivePrefix('gift','active') }}" aria-controls="gift_menu" role="button" aria-expanded="{{ isActivePrefix('gift','true') }}">
                    <i class="material-icons-round opacity-10">redeem</i>
                    <span class="nav-link-text ms-2 ps-1">Gift</span>
                </a>
                <div class="collapse {{ isActivePrefix('gift','show') }}" id="gift_menu">
                    <ul class="nav ">
                        <li class="nav-item {{isActive('gift.list')}}">
                            <a class="nav-link text-white {{isActive('gift.list')}}" href="{{route('gift.list')}}">
                                <i class="material-icons opacity-10">list_alt</i>
                                <span class="sidenav-normal  ms-2  ps-1">List</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#notification_menu" class="nav-link text-white {{ isActivePrefix('notification','active') }}" aria-controls="notification_menu" role="button" aria-expanded="{{ isActivePrefix('notification','true') }}">
                    <i class="material-icons-round opacity-10">notifications</i>
                    <span class="nav-link-text ms-2 ps-1">Notifications</span>
                </a>
                <div class="collapse {{ isActivePrefix('notification','show') }}" id="notification_menu">
                    <ul class="nav ">
                        <li class="nav-item {{isActive('notification.index')}}">
                            <a class="nav-link text-white {{isActive('notification.index')}}" href="{{route('notification.index')}}">
                                <i class="material-icons opacity-10">send</i>
                                <span class="sidenav-normal  ms-2  ps-1">Send</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#report_menu" class="nav-link text-white {{ isActivePrefix('report','active') }}" aria-controls="report_menu" role="button" aria-expanded="{{ isActivePrefix('report','true') }}">
                    <i class="material-icons-round opacity-10">report</i>
                    <span class="nav-link-text ms-2 ps-1">Report</span>
                </a>
                <div class="collapse {{ isActivePrefix('report','show') }}" id="report_menu">
                    <ul class="nav ">
                        <li class="nav-item {{isActive('report.list')}}">
                            <a class="nav-link text-white {{isActive('report.list')}}" href="{{route('report.list')}}">
                                <i class="material-icons opacity-10">list_alt</i>
                                <span class="sidenav-normal  ms-2  ps-1">List</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</aside>