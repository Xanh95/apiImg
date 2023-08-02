<?php

namespace App\Providers;

use App\Models\ReversionArticle;
use App\Models\Toppage;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Policies\PostPolicy;
use App\Policies\ArticlePolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ReversionArticlePolicy;
use App\Policies\TopPagePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\Model' => 'App\Policies\ModelPolicy',
        User::class => UserPolicy::class,
        Post::class => PostPolicy::class,
        Article::class => ArticlePolicy::class,
        Category::class => CategoryPolicy::class,
        ReversionArticle::class => ReversionArticlePolicy::class,
        Toppage::class => TopPagePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        //
    }
}
