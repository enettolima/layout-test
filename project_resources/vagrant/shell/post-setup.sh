#!/bin/bash

# Remove the default index.html that I guess Apache install drops
if [[ -f /var/www/index.html ]]; then
    rm /var/www/index.html
fi

# Fix the frikkin' timezone
echo "America/Chicago" > /etc/timezone
dpkg-reconfigure -f noninteractive tzdata

# Get me some Boris up in here
git clone https://github.com/d11wtq/boris.git /usr/local/boris
ln -s /usr/local/boris/bin/boris /usr/local/bin/boris
