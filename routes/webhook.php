<?php

use Illuminate\Http\Request;
Route::any('/globalsim', 'WebhookController@globalsim');
Route::any('/stripe', 'WebhookController@stripe');
