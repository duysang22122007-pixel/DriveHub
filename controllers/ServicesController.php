<?php

class ServicesController
{
    public function index()
    {
        $title = 'DriveHub - Dịch vụ bảo dưỡng ô tô';
        $view = 'services';

        $values = [
            [
                'icon' => 'inspect',
                'title' => 'Kiểm tra xe kỹ lưỡng',
                'desc' => 'Đánh giá tình trạng thực tế trước khi đề xuất hạng mục bảo dưỡng hoặc sửa chữa.',
            ],
            [
                'icon' => 'process',
                'title' => 'Quy trình thực hiện rõ ràng',
                'desc' => 'Các bước tiếp nhận, kiểm tra, tư vấn và bàn giao được thực hiện theo trình tự.',
            ],
            [
                'icon' => 'consult',
                'title' => 'Tư vấn trước khi sửa chữa',
                'desc' => 'Giải thích rõ tình trạng xe, phương án xử lý và chi phí dự kiến trước khi thực hiện.',
            ],
            [
                'icon' => 'cost',
                'title' => 'Chi phí minh bạch',
                'desc' => 'Thông tin giá tham khảo được công khai; hạng mục phát sinh sẽ được trao đổi trước.',
            ],
        ];

        $services = [
            [
                'slug' => 'bao-duong-dinh-ky',
                'name' => 'Bảo dưỡng định kỳ',
                'description' => 'Kiểm tra tổng thể các bộ phận quan trọng của xe, phát hiện những dấu hiệu bất thường và thực hiện các hạng mục bảo dưỡng cần thiết.',
                'tasks' => [
                    'Kiểm tra tổng thể tình trạng xe.',
                    'Kiểm tra dầu động cơ và các loại dung dịch.',
                    'Kiểm tra lọc và các bộ phận cơ bản.',
                    'Tư vấn các hạng mục cần bảo dưỡng.',
                ],
                'price' => '500.000 – 1.500.000 VNĐ',
                'price_short' => '500.000 – 1.500.000đ',
                'duration' => '2 – 3 giờ',
                'image' => 'service-maintenance.jpg',
                'alt' => 'Xe được nâng kiểm tra tại gara DriveHub',
                'table_summary' => 'Kiểm tra và bảo dưỡng tổng thể',
            ],
            [
                'slug' => 'thay-dau-dong-co',
                'name' => 'Thay dầu động cơ',
                'description' => 'Thay dầu động cơ giúp duy trì khả năng bôi trơn, hỗ trợ giảm ma sát và bảo vệ động cơ trong quá trình vận hành.',
                'tasks' => [
                    'Xả dầu động cơ cũ.',
                    'Kiểm tra và thay dầu mới.',
                    'Kiểm tra lọc dầu khi cần thiết.',
                    'Kiểm tra mức dầu và tình trạng rò rỉ.',
                ],
                'price' => '500.000 – 1.200.000 VNĐ',
                'price_short' => '500.000 – 1.200.000đ',
                'duration' => '30 – 60 phút',
                'image' => 'service-oil.jpg',
                'alt' => 'Kỹ thuật viên thay dầu động cơ',
                'table_summary' => 'Thay dầu và kiểm tra lọc dầu',
            ],
            [
                'slug' => 'kiem-tra-sua-chua-phanh',
                'name' => 'Kiểm tra và sửa chữa hệ thống phanh',
                'description' => 'Kiểm tra tình trạng hoạt động của hệ thống phanh nhằm phát hiện sớm các vấn đề ảnh hưởng đến khả năng giảm tốc và an toàn khi lái xe.',
                'tasks' => [
                    'Kiểm tra má phanh.',
                    'Kiểm tra đĩa phanh.',
                    'Kiểm tra dầu phanh.',
                    'Kiểm tra khả năng hoạt động của hệ thống phanh.',
                    'Tư vấn thay thế khi cần thiết.',
                ],
                'price' => '300.000 – 2.500.000 VNĐ',
                'price_short' => '300.000 – 2.500.000đ',
                'duration' => '1 – 3 giờ',
                'image' => 'service-brake.jpg',
                'alt' => 'Kỹ thuật viên kiểm tra hệ thống phanh',
                'table_summary' => 'Kiểm tra hệ thống phanh',
            ],
            [
                'slug' => 'kiem-tra-sua-chua-dieu-hoa',
                'name' => 'Kiểm tra và sửa chữa điều hòa',
                'description' => 'Kiểm tra và xử lý các vấn đề liên quan đến hệ thống điều hòa nhằm duy trì khả năng làm mát và sự thoải mái trong khoang xe.',
                'tasks' => [
                    'Kiểm tra khả năng làm mát.',
                    'Kiểm tra lọc gió điều hòa.',
                    'Kiểm tra hệ thống điều hòa.',
                    'Vệ sinh hoặc xử lý các vấn đề phát sinh.',
                ],
                'price' => '300.000 – 1.500.000 VNĐ',
                'price_short' => '300.000 – 1.500.000đ',
                'duration' => '1 – 2 giờ',
                'image' => 'service-ac.jpg',
                'alt' => 'Cửa gió điều hòa trên xe ô tô',
                'table_summary' => 'Kiểm tra và xử lý điều hòa',
            ],
            [
                'slug' => 'kiem-tra-thay-ac-quy',
                'name' => 'Kiểm tra và thay ắc quy',
                'description' => 'Kiểm tra tình trạng hoạt động của ắc quy và hệ thống sạc để đảm bảo khả năng khởi động và cung cấp điện cho xe.',
                'tasks' => [
                    'Kiểm tra điện áp ắc quy.',
                    'Kiểm tra tình trạng bình.',
                    'Kiểm tra hệ thống sạc.',
                    'Tư vấn thay bình phù hợp với dòng xe.',
                ],
                'price' => '1.200.000 – 3.500.000 VNĐ',
                'price_short' => '1.200.000 – 3.500.000đ',
                'duration' => '30 – 60 phút',
                'image' => 'service-battery.jpg',
                'alt' => 'Kiểm tra ắc quy trong khoang động cơ',
                'table_summary' => 'Kiểm tra và thay bình ắc quy',
            ],
            [
                'slug' => 'kiem-tra-cham-soc-lop',
                'name' => 'Kiểm tra và chăm sóc lốp xe',
                'description' => 'Kiểm tra tình trạng lốp giúp phát hiện độ mòn, áp suất không phù hợp và các dấu hiệu bất thường ảnh hưởng đến khả năng vận hành của xe.',
                'tasks' => [
                    'Kiểm tra áp suất lốp.',
                    'Kiểm tra độ mòn và tình trạng lốp.',
                    'Tư vấn cân bằng và đảo lốp.',
                    'Tư vấn thay thế khi cần thiết.',
                ],
                'price' => '100.000 – 1.500.000 VNĐ',
                'price_short' => '100.000 – 1.500.000đ',
                'duration' => '30 – 90 phút',
                'image' => 'service-tire.jpg',
                'alt' => 'Bánh xe và cụm phanh được kiểm tra',
                'table_summary' => 'Kiểm tra và chăm sóc lốp',
            ],
        ];

        $processSteps = [
            [
                'title' => 'Tiếp nhận yêu cầu',
                'desc' => 'Tiếp nhận thông tin và nhu cầu của khách hàng.',
            ],
            [
                'title' => 'Kiểm tra tình trạng xe',
                'desc' => 'Kiểm tra các bộ phận liên quan và xác định vấn đề.',
            ],
            [
                'title' => 'Tư vấn và báo giá',
                'desc' => 'Giải thích hạng mục cần thực hiện và chi phí dự kiến.',
            ],
            [
                'title' => 'Tiến hành dịch vụ',
                'desc' => 'Thực hiện bảo dưỡng hoặc sửa chữa theo nội dung đã thống nhất.',
            ],
            [
                'title' => 'Kiểm tra và bàn giao',
                'desc' => 'Kiểm tra lại và bàn giao xe cho khách hàng.',
            ],
        ];

        $faqs = [
            [
                'question' => 'Bao lâu nên bảo dưỡng ô tô một lần?',
                'answer' => 'Lịch bảo dưỡng phụ thuộc vào dòng xe, quãng đường và điều kiện sử dụng. Thông thường nên kiểm tra xe định kỳ theo khuyến nghị của nhà sản xuất hoặc khi nhận thấy dấu hiệu bất thường.',
            ],
            [
                'question' => 'Thời gian bảo dưỡng mất bao lâu?',
                'answer' => 'Thời gian thực hiện tùy thuộc vào từng hạng mục. Kiểm tra nhanh có thể mất 30 – 60 phút, bảo dưỡng định kỳ thường mất 2 – 3 giờ. DriveHub sẽ thông báo thời gian ước tính trước khi thực hiện.',
            ],
            [
                'question' => 'Giá dịch vụ có phát sinh thêm không?',
                'answer' => 'Giá hiển thị chỉ mang tính tham khảo. Nếu phát sinh hạng mục cần xử lý, DriveHub sẽ tư vấn và xác nhận với khách hàng trước khi tiến hành.',
            ],
            [
                'question' => 'Có cần đặt lịch trước không?',
                'answer' => 'Nên đặt lịch trước để chủ động thời gian và hạn chế chờ đợi. Chức năng đặt lịch trực tuyến đang chờ tích hợp; thông tin liên hệ chính thức sẽ được cập nhật khi hoàn thiện.',
            ],
            [
                'question' => 'Tôi có được tư vấn trước khi sửa chữa không?',
                'answer' => 'Có. DriveHub kiểm tra tình trạng xe, giải thích các hạng mục cần thực hiện và chi phí dự kiến trước khi khách hàng quyết định sửa chữa.',
            ],
        ];

        require_once PATH_VIEW_MAIN;
    }
}
