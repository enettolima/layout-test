<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element"> <span>
                    <img alt="image" class="img-circle" src="{{ asset('img/profile_small.jpg') }}" />
                     </span>
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                    <span class="clear"> <span class="block m-t-xs"> <strong class="font-bold">Justin Casey</strong>
                     </span> <span class="text-muted text-xs block">Buyer <b class="caret"></b></span> </span> </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs">
                        <li><a href="profile.html">Profile</a></li>
                        <li><a href="contacts.html">Contacts</a></li>
                        <li><a href="mailbox.html">Mailbox</a></li>
                        <li class="divider"></li>
                        <li><a href="login.html">Logout</a></li>
                    </ul>
                </div>
                <div class="logo-element">
                    IN+
                </div>
            </li>
            <li class="active">
                <a href="/dashboard"><i class="fa fa-diamond"></i> <span class="nav-label">Dashboard</span></a>
            </li>
            <li>
                <a href="index.html"><i class="fa fa-th-large"></i> <span class="nav-label">E-Procurement</span> <span class="fa arrow"></span></a>
                <ul class="nav nav-second-level collapse">
                    <li><a href="mailbox.html"><span class="nav-label">Orders </span></a></a>
                      <ul class="nav nav-third-level">
                        <li><a href="/order/requests">Request</a></li>
                        <li><a href="/order/approval">Approval</a></li>
                        <li><a href="/order/importing">Importing</a></li>
                        <li><a href="/order/payment">Payment</a></li>
                        <li><a href="/order/receive">Receive</a></li>
                      </ul>
                    </li>
                    <li><a href="/vendors">Vendors</a></li>
                    <li><a href="/catalog">Catalog</a></li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
