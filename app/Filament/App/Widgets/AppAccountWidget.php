<?php

namespace App\Filament\App\Widgets;

use Filament\Widgets\Widget;

class AppAccountWidget extends Widget
{
    /**
     * We are extending the base widget, so it will have all the same
     * functionality, but it will be correctly scoped to our 'app' panel
     * and its 'tenant' guard, ensuring it always gets the Tenant model.
     */
}
