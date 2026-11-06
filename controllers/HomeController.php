<?php

class HomeController
{
    public function index()
    {
        // Tiêu đề trang chủ
        $title = 'DriveHub - Chăm sóc xe toàn diện';

        // Xác định View cần hiển thị
        $view = 'home';

        // Gọi Layout chính
        require_once PATH_VIEW_MAIN;
    }
}
