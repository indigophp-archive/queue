Phresque - Queue management for PHP 5.3+
========================================

Phresque manages your queues and processes the jobs you put onto them.


Why this is not and cannot be a complete solution?
--------------------------------------------------

It is hard to implement this logic fully framework independent way as a complete solution.

First of all you probably will run your Workers from console. Every framework has its own and quite useful way to create console applications (eg. Fuel's `oil` or Laravel's `artisan`). There is no sense in creating a platform independent console application but without it this is not complete. You have to create your own wrapper around this package to be able to run Workers in console.

__Note__: This will probably change in future versions.

Another problem is that it is hard to decide whether an application can always use a Queue or not. For example: there is a network outage and your queue server is unavailable. In this case you cannot queue any events. So this is the second thing you have to implement in your wrapper.