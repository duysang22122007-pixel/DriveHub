<?php

class AdminDashboardController
{
    public function index(): void
    {
        require_role('admin');
        $title = 'Tổng quan quản trị - DriveHub';
        $view = 'admin/dashboard';
        require_once PATH_VIEW_MAIN;
    }
}
