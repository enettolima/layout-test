        <!-- Fixed navbar -->
        <div class="navbar navbar-default navbar-fixed-top" role="navigation">
            <div class="container">
                <div class="navbar-header">
                    <a class="navbar-brand passport-brand" href="/"><img alt="EBT Passport Logo" src="/images/logo.png"></a>
                </div>
                <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav">
                    @if(Auth::check())
                        <li class="<?php echo Request::is('home*') ? 'active' : '' ?>"><a href="/">Passport Home</a></li>
                        <li class="<?php echo Request::is('scheduler*') ? 'active' : '' ?>"><a href="/scheduler">Scheduler</a></li>
                        <li class="<?php echo Request::is('weborder*') ? 'active' : '' ?>"><a href="/weborder">Web Order</a></li>
                    @endif
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                    @if(Auth::check())
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">STORE 301 <b class="caret" /></b></a>
                            <ul class="dropdown-menu">
                                <li class="dropdown-header">Choose store:</li>
                                <li><a href="#">301</a></li>
                                <li><a href="#">302</a></li>
                                <li><a href="#">303</a></li>
                                <li><a href="#">304</a></li>
                            </ul>
                        </li>
                        <li class="<?php echo Request::is('settings*') ? 'active' : '' ?>"><a href="/settings"><span class="glyphicon glyphicon-cog"></span></a></li>
                    @endif
                    </ul>
                </div><!--/.nav-collapse -->
            </div>
        </div>
