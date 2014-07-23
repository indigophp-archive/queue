#!/bin/bash

sudo rabbitmqctl add_vhost queue_vhost
sudo rabbitmqctl add_user queue_user queue_pass
sudo rabbitmqctl set_permissions -p queue_vhost queue_user ".*" ".*" ".*"
