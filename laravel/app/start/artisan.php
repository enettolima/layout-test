<?php

/*
|--------------------------------------------------------------------------
| Register The Artisan Commands
|--------------------------------------------------------------------------
|
| Each available Artisan command must be registered with the console so
| that it is available to be called. We'll register every command so
| the console gets access to each of the command object instances.
|
*/

Artisan::add(new CronRefreshStores);

Artisan::add(new CronRefreshEmployees);

Artisan::add(new CronWeatherGetAlerts);

Artisan::add(new RpmsyncCompareOrders);

Artisan::add(new CronRPMagentoInventorySync);

Artisan::add(new CronGetMagentoShippingData);
