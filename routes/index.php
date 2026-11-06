<?php

$action = $_GET['action'] ?? '/';

match ($action) {
    '/'         => (new HomeController)->index(),
    'services'  => (new ServicesController)->index(),
    'booking'   => (new BookingController)->index(),
};