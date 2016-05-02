        <!-- Fixed navbar -->
        <div class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand passport-brand" href="/"><img alt="EBT Passport Logo" src="/images/logo.png"></a>
                </div>
                <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                    @if(Auth::check())
                            <li class="<?php echo Request::is('documents*') ?  'active' : '' ?>"><a href="/documents">Docs</a></li>
                            <li class="<?php echo Request::is('scheduler*') ? 'active' : '' ?>"><a href="/scheduler">Scheduler</a></li>
                            <li class="<?php echo Request::is('reports*') ? 'active' : '' ?>"><a href="/reports">Reports</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">Tools</a>
                            <ul class="dropdown-menu">
                                <!--<li class="<?php echo Request::is('tools/web-order*') ?  'active' : '' ?>"><a href="/tools/web-order">Reorder</a></li>-->
                                <li class="<?php echo Request::is('restock*') ?  'active' : '' ?>"><a href="/restock">Reorder</a></li>
                                <li><a target="_blank" href="http://blog.earthboundtrading.com/images/nf/signorderform/">Sign Orders</a></li>
                                <li><a target="_blank" href="http://blog.earthboundtrading.com/images/nf/inventorytransfer/">Inventory Transfer</a></li>
                                <li class="<?php echo Request::is('tools/employee-lookup*') ?  'active' : '' ?>"><a href="/tools/employee-lookup">Employee Lookup</a></li>
                                <li class="<?php echo Request::is('tools/exempt-form*') ?  'active' : '' ?>"><a href="/tools/exempt-form">Tax Exempt Form</a></li>
                                <li class="<?php echo Request::is('tools/music-request*') ?  'active' : '' ?>"><a href="/tools/music-request">Music Request</a></li>
                                @if (Auth::user()->hasRole('EBTPERM_PIMSEDIT'))
                                    <li class="<?php echo Request::is('pims*') ? 'active' : '' ?>"><a href="/pims">Product Info Management</a></li>
                                @endif
                            </ul>
                        </li>

                        <li><a target="_blank" href="http://ebt.bz/newpassporthelp">Help</a></li>

                        @if(Auth::user()->hasRole('Developer'))
                            <!--<li class="<?php echo Request::is('dev*') ? 'active' : '' ?>"><a href="/dev">Dev</a></li>-->
                        @endif

                        @if(Auth::user()->hasRole('EBTPERM_PASSPORTADMIN'))
                            <li class="<?php echo Request::is('admin*') ? 'active' : '' ?>"><a href="/admin">Admin</a></li>
                        @endif
                    @endif
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                    @if(Auth::check())
                        <li class="dropdown">
                        <a href="#" class="dropdown-toggle text-danger" data-toggle="dropdown">

                            <?php
                                if (Session::has('storeContext')) {
									if (Session::get('storeContext') == '000') {
										$storeName = 'Corporate';
									} else {
										$storeName = StoresLookup::where('code', Session::get('storeContext'))->first()->store_name;
                    $storeName = strlen($storeName) > 10 ? substr($storeName,0,10)."..." : $storeName;
									}
                                    //$sl = StoresLookup::where('code', Session::get('storeContext'))->first();
                                echo '<strong class="text-primary">Store: <span id="current-store">' . Session::get('storeContext') . '</span><span id="current-store-name"> - '.$storeName.'</span></strong>';
                                } else {
                                    echo "<em>Please Choose Store</em>";
                                }
                            ?>

                            <b class="caret" /></b></a>

                            <ul class="dropdown-menu">
                                <li class="dropdown-header">Switch Store To:</li>
                                <?php
                                    $sr = StoresResolver::getInstance();

                                    $stores = Auth::user()->getStores();

                                    sort($stores);

                                    foreach ($stores as $store) {
                                        $sl = $sr->getStore($store)->store_name;
                                        echo "<li><a data-store-number=\"$store\" class=\"change-store-context\" href=\"#\">$store - $sl</a></li>";
                                    }
                                ?>
                            </ul>
                        </li>

                        <li class="dropdown <?php echo Request::is('preferences*') ? 'active' : '' ?>">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
								<?php echo Auth::user()->username ?> (<?php echo Auth::user()->full_name ?>)<b class="caret" /></b></a>
                            <ul class="dropdown-menu">
                                <li class="dropdown-header"><?php echo Auth::user()->fname . ' ' . Auth::user()->lname ?></li>
                                <li class="<?php echo Request::is('preferences*') ? 'active' : '' ?>"><a href="/preferences">Preferences</a></li>
                                <li><a href="/users/logout">Logout</a></li>
                            </ul>
                        </li>
                    @endif
                    </ul>
                </div>
                <!--/.nav-collapse -->
            </div>
        </div>
