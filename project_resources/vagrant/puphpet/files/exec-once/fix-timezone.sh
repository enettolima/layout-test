#!/bin/bash

# Fix the frikkin' timezone
echo "America/Chicago" > /etc/timezone
dpkg-reconfigure -f noninteractive tzdata
