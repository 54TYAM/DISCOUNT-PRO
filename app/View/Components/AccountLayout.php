<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Account-context layout. Renders the admin sidebar layout for managers / admins
 * and the customer top-bar layout for everyone else.
 *
 * Use on any page that should keep the user's normal navigation regardless of
 * who's viewing it (Profile settings, account-level pages, etc).
 */
class AccountLayout extends Component
{
    public function __construct(public string $title = 'My Account') {}

    public function render(): View
    {
        $isManager = auth()->user()?->isManager() ?? false;
        return view($isManager ? 'layouts.admin' : 'layouts.app');
    }
}
