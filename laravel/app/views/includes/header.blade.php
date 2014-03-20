        <!-- Fixed navbar -->
        <div class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand passport-brand" href="/"><img alt="EBT Passport Logo" src="/images/logo.png"></a>
                </div>
                <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                    @if(Auth::check())
                        <!--<li class="<?php echo Request::is('home*') ? 'active' : '' ?>"><a href="/">Home</a></li>-->
                        <li class="<?php echo Request::is('scheduler*') ? 'active' : '' ?>"><a href="/scheduler">Scheduler</a></li>
                        <li class="<?php echo Request::is('weborder*') ? 'active' : '' ?>"><a href="/weborder">Web Order</a></li>
                    @endif
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                    @if(Auth::check())
                        <li class="dropdown">
                        <a href="#" class="dropdown-toggle text-danger" data-toggle="dropdown">

                            <?php
                                if (Session::has('storeContext')) {
                                    echo '<strong>Current Store: <span id="current-store">' . Session::get('storeContext') . " - Shady Brook, AL</span></strong>";
                                } else {
                                    echo "<em>Please Choose Store</em>";
                                }
                            ?>

                            <b class="caret" /></b></a>

                            <ul class="dropdown-menu">
                                <li class="dropdown-header">Switch Store To:</li>
                                <?php
                                    foreach (Auth::user()->getStores() as $store) {
                                        echo "<li><a data-store-number=\"$store\" class=\"change-store-context\" href=\"#\">$store</a></li>";
                                    }
                                ?>
                            </ul>
                        </li>

                        <li class="dropdown <?php echo Request::is('settings*') ? 'active' : '' ?>">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo Auth::user()->username ?> <b class="caret" /></b></a>
                            <ul class="dropdown-menu">
                                <li class="dropdown-header"><?php echo Auth::user()->fname . ' ' . Auth::user()->lname ?></li>
                                <li class="<?php echo Request::is('settings*') ? 'active' : '' ?>"><a href="/settings">Settings</a></li>
                                <li><a href="/users/logout">Logout</a></li>
                            </ul>
                        </li>
                    @endif
                    </ul>
                </div>
                <!--/.nav-collapse -->
            </div>
        </div>
