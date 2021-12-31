=======================
Setting up the Siteminder Authentication plugin for Booked Scheduler
=======================

The authentication is done by the Siteminder reverse proxy. The username is obtained
from the request header with the key 'sm_user'

Make sure that it's working there correctly, because we have here almost no
additional checks on the authentication.
