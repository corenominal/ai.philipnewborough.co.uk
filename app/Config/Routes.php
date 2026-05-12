<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Chat session deep link (must be before chat API routes)
$routes->get('/chat/api/models', 'ChatApi::models');
$routes->get('/chat/api/search', 'ChatApi::search');
$routes->get('/chat/api/sessions', 'ChatApi::sessions');
$routes->post('/chat/api/session', 'ChatApi::createSession');
$routes->patch('/chat/api/session/(:segment)', 'ChatApi::updateSession/$1');
$routes->delete('/chat/api/session/(:segment)', 'ChatApi::deleteSession/$1');
$routes->get('/chat/api/messages/(:segment)', 'ChatApi::messages/$1');
$routes->post('/chat/api/stream', 'ChatApi::stream');
$routes->get('/chat/(:segment)', 'Home::chatSession/$1');

// Admin routes
$routes->get('/admin', 'Admin\Home::index');
$routes->get('/admin/prompt', 'Admin\Prompt::index');
$routes->get('/admin/api', 'Admin\Api::index');
$routes->post('/admin/prompt/update', 'Admin\Prompt::update');
$routes->post('/admin/prompt/revert/(:num)', 'Admin\Prompt::revert/$1');

// API routes
$routes->match(['get', 'options'], '/api/test/ping', 'Api\Test::ping');
$routes->match(['post', 'options'], '/api/status/rewrite', 'Api\Status::rewrite');
$routes->match(['post', 'options'], '/api/images/alttext', 'Api\Images::alttext');
$routes->match(['post', 'options'], '/api/images/describe', 'Api\Images::describe');
$routes->match(['post', 'options'], '/api/blog/analyse', 'Api\Blog::analyse');
$routes->match(['post', 'options'], '/api/blog/rewrite', 'Api\Blog::rewrite');
$routes->match(['post', 'options'], '/api/blog/excerpt', 'Api\Blog::excerpt');
$routes->match(['post', 'options'], '/api/blog/creative', 'Api\Blog::creative');
$routes->match(['post', 'options'], '/api/blog/outline', 'Api\Blog::outline');
$routes->match(['post', 'options'], '/api/tags/generate', 'Api\Tags::generate');
$routes->match(['get', 'options'], '/api/ollama/list', 'Api\Ollama::list');

// Command line routes
$routes->cli('cli/test/index/(:segment)', 'CLI\Test::index/$1');
$routes->cli('cli/test/count', 'CLI\Test::count');

// Metrics route
$routes->post('/metrics/receive', 'Metrics::receive');

// Logout route
$routes->get('/logout', 'Auth::logout');

// Unauthorised route
$routes->get('/unauthorised', 'Unauthorised::index');

// Custom 404 route
$routes->set404Override('App\Controllers\Errors::show404');

// Debug routes
$routes->get('/debug', 'Debug\Home::index');
$routes->get('/debug/(:segment)', 'Debug\Rerouter::reroute/$1');
$routes->get('/debug/(:segment)/(:segment)', 'Debug\Rerouter::reroute/$1/$2');
