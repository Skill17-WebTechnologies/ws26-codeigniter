<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');

// Database connection check — the same URL every WSC2026 template answers on.
$routes->get('api/db-check', 'DbCheck::index');
