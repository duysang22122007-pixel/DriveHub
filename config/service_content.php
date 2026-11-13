<?php

declare(strict_types=1);

return [
    'Bảo dưỡng cấp 1 – 5.000 km' => [
        'description' => 'Gói bảo dưỡng mốc 5.000 km tập trung kiểm tra nhanh các hệ thống thiết yếu, thay dầu và bổ sung dung dịch theo khuyến nghị của nhà sản xuất.',
        'benefits' => [
            'Duy trì độ ổn định của động cơ trong giai đoạn đầu sử dụng.',
            'Phát hiện sớm dấu hiệu mài mòn hoặc rò rỉ.',
            'Giúp xe sẵn sàng cho các hành trình ngắn và di chuyển hằng ngày.',
        ],
        'items' => [
            'Thay dầu động cơ và lọc dầu.',
            'Kiểm tra mức nước làm mát, dầu phanh và nước rửa kính.',
            'Kiểm tra lốp, áp suất lốp và hệ thống chiếu sáng.',
            'Kiểm tra nhanh gầm, phanh và các tiếng kêu bất thường.',
        ],
        'process' => [
            'Tiếp nhận xe và ghi nhận tình trạng ban đầu.',
            'Thực hiện thay dầu, lọc và bổ sung dung dịch.',
            'Kiểm tra các hạng mục an toàn theo danh mục.',
            'Bàn giao xe kèm ghi chú các hạng mục cần theo dõi.',
        ],
        'notes' => [
            'Nên bảo dưỡng theo đúng mốc km hoặc thời gian khuyến nghị.',
            'Thông báo cho kỹ thuật viên nếu xe có tiếng kêu hoặc đèn cảnh báo.',
        ],
    ],
    'Bảo dưỡng cấp 2 – 10.000 km' => [
        'description' => 'Mốc 10.000 km mở rộng kiểm tra động cơ, hệ thống treo và các bộ phận hao mòn, đồng thời thay thế vật tư định kỳ.',
        'benefits' => [
            'Giữ xe vận hành êm và ổn định.',
            'Giảm nguy cơ hư hỏng dây chuyền do bỏ qua bảo dưỡng.',
            'Có cơ sở theo dõi lịch sử chăm sóc xe.',
        ],
        'items' => [
            'Thay dầu và lọc dầu.',
            'Kiểm tra lọc gió động cơ và lọc gió điều hòa.',
            'Kiểm tra phanh, lốp, hệ thống treo và rotuyn.',
            'Kiểm tra ắc quy, dây đai và các dung dịch.',
        ],
        'process' => [
            'Kiểm tra lịch sử bảo dưỡng và tình trạng thực tế.',
            'Thay thế vật tư đến hạn.',
            'Kiểm tra chi tiết các hệ thống theo mốc 10.000 km.',
            'Chạy thử, bàn giao và tư vấn mốc tiếp theo.',
        ],
        'notes' => [
            'Thời gian thực hiện phụ thuộc tình trạng xe và vật tư cần thay.',
            'Nên vệ sinh khoang máy định kỳ để dễ phát hiện rò rỉ.',
        ],
    ],
    'Bảo dưỡng cấp 3 – 20.000 km' => [
        'description' => 'Gói bảo dưỡng 20.000 km kiểm tra sâu hơn các hệ thống quan trọng, tập trung vào độ mài mòn và hiệu quả vận hành.',
        'benefits' => [
            'Duy trì hiệu suất động cơ và khả năng tiết kiệm nhiên liệu.',
            'Phát hiện sớm các hư hỏng ở gầm, lái và phanh.',
            'Kéo dài tuổi thọ các bộ phận quan trọng.',
        ],
        'items' => [
            'Thay dầu, lọc dầu và kiểm tra lọc nhiên liệu nếu cần.',
            'Kiểm tra bugi, dây đai, bơm nước và hệ thống làm mát.',
            'Kiểm tra phanh, đĩa phanh, má phanh và dầu phanh.',
            'Kiểm tra hệ thống treo, cao su gầm và cân bằng lốp.',
        ],
        'process' => [
            'Tiếp nhận và đối chiếu lịch sử bảo dưỡng.',
            'Kiểm tra tổng quát bằng thiết bị và quan sát trực tiếp.',
            'Thay thế hoặc hiệu chỉnh các hạng mục đến hạn.',
            'Chạy thử, kiểm tra lỗi và bàn giao.',
        ],
        'notes' => [
            'Một số hạng mục chỉ thay khi kiểm tra thấy thực sự cần thiết.',
            'Khách hàng nên xác nhận danh mục thay thế trước khi thực hiện.',
        ],
    ],
    'Bảo dưỡng cấp 4 – 40.000 km+' => [
        'description' => 'Bảo dưỡng lớn cho các mốc từ 40.000 km, kiểm tra toàn diện và thay thế các bộ phận hao mòn theo khuyến nghị.',
        'benefits' => [
            'Khôi phục độ ổn định và độ tin cậy của xe.',
            'Giảm rủi ro sự cố trên hành trình dài.',
            'Có kế hoạch bảo trì rõ ràng cho giai đoạn tiếp theo.',
        ],
        'items' => [
            'Thay dầu, lọc và các dung dịch đến hạn.',
            'Kiểm tra hệ thống làm mát, nhiên liệu và đánh lửa.',
            'Kiểm tra gầm, lái, phanh, treo và hệ thống điện.',
            'Kiểm tra lốp, mâm và các chi tiết cao su.',
        ],
        'process' => [
            'Kiểm tra tổng thể và lập danh mục hạng mục cần xử lý.',
            'Trao đổi với khách hàng về phương án và vật tư.',
            'Thực hiện bảo dưỡng, thay thế theo danh mục đã thống nhất.',
            'Chạy thử, kiểm tra chất lượng và bàn giao.',
        ],
        'notes' => [
            'Thời gian và chi phí phụ thuộc kết quả kiểm tra thực tế.',
            'Nên thực hiện tại cơ sở có thiết bị chẩn đoán phù hợp.',
        ],
    ],
    'Kiểm tra xe trước chuyến đi xa / trước đăng kiểm' => [
        'description' => 'Kiểm tra tổng quát các hệ thống an toàn và vận hành trước hành trình dài hoặc trước kỳ đăng kiểm.',
        'benefits' => [
            'Chủ động phát hiện lỗi trước khi lên đường.',
            'Tăng độ an tâm cho hành trình dài.',
            'Hạn chế các phát sinh không mong muốn trong quá trình đăng kiểm.',
        ],
        'items' => [
            'Kiểm tra lốp, áp suất lốp và bánh dự phòng.',
            'Kiểm tra phanh, đèn, còi, gạt mưa và dung dịch.',
            'Kiểm tra động cơ, gầm, hệ thống điện và rò rỉ.',
            'Đọc lỗi bằng thiết bị chẩn đoán nếu có.',
        ],
        'process' => [
            'Ghi nhận kế hoạch di chuyển và các dấu hiệu bất thường.',
            'Kiểm tra theo danh mục an toàn trước chuyến đi.',
            'Tư vấn các hạng mục cần xử lý ngay hoặc theo dõi.',
            'Bàn giao kèm danh sách lưu ý cho hành trình.',
        ],
        'notes' => [
            'Nên kiểm tra trước chuyến đi ít nhất vài ngày để kịp xử lý.',
            'Một số lỗi đăng kiểm cần thời gian đặt phụ tùng.',
        ],
    ],
    'Sửa chữa động cơ – hộp số' => [
        'description' => 'Chẩn đoán và sửa chữa các lỗi liên quan đến động cơ, hộp số, hệ thống truyền động và các tiếng kêu bất thường.',
        'benefits' => [
            'Khắc phục đúng nguyên nhân thay vì chỉ xử lý triệu chứng.',
            'Giúp xe vận hành ổn định và an toàn hơn.',
            'Có phương án sửa chữa phù hợp với tình trạng thực tế.',
        ],
        'items' => [
            'Đọc lỗi và kiểm tra áp suất, nhiệt độ, tiếng ồn động cơ.',
            'Kiểm tra hộp số, ly hợp và hệ thống truyền động.',
            'Kiểm tra rò rỉ dầu, nước làm mát và các đường ống.',
            'Thay thế hoặc phục hồi các chi tiết hỏng theo kết luận.',
        ],
        'process' => [
            'Tiếp nhận mô tả lỗi và kiểm tra sơ bộ.',
            'Chẩn đoán bằng thiết bị và kiểm tra trực tiếp.',
            'Trao đổi phương án sửa chữa, vật tư và thời gian.',
            'Thực hiện sửa chữa, chạy thử và bàn giao.',
        ],
        'notes' => [
            'Không nên tiếp tục vận hành khi có tiếng kêu lớn hoặc đèn cảnh báo.',
            'Cần xác nhận phương án trước khi tháo rã các cụm chi tiết.',
        ],
    ],
    'Sửa chữa hệ thống gầm – lái – phanh' => [
        'description' => 'Kiểm tra và xử lý các hư hỏng ở gầm, hệ thống lái, treo và phanh để đảm bảo độ an toàn khi vận hành.',
        'benefits' => [
            'Cải thiện độ ổn định và cảm giác lái.',
            'Giảm tiếng kêu, rung lắc và mài mòn lốp.',
            'Đảm bảo hiệu quả phanh trong các tình huống khẩn cấp.',
        ],
        'items' => [
            'Kiểm tra rotuyn, cao su gầm, giảm xóc và lò xo.',
            'Kiểm tra thước lái, trợ lực lái và độ chụm bánh.',
            'Kiểm tra má phanh, đĩa phanh, dầu phanh và tang trống.',
            'Cân chỉnh hoặc thay thế chi tiết theo kết quả kiểm tra.',
        ],
        'process' => [
            'Kiểm tra trên cầu nâng và chạy thử để ghi nhận triệu chứng.',
            'Xác định chi tiết hư hỏng và mức độ ảnh hưởng.',
            'Thống nhất phương án sửa chữa với khách hàng.',
            'Thực hiện, kiểm tra lại và bàn giao.',
        ],
        'notes' => [
            'Các hạng mục phanh cần được kiểm tra ngay khi có dấu hiệu bất thường.',
            'Nên cân chỉnh thước lái sau khi sửa chữa gầm.',
        ],
    ],
    'Sửa chữa hệ thống điện – điện lạnh' => [
        'description' => 'Chẩn đoán lỗi điện, ắc quy, hệ thống sạc, điều hòa và điện lạnh trên xe.',
        'benefits' => [
            'Khắc phục lỗi khởi động, sạc và các thiết bị điện.',
            'Duy trì hiệu quả làm mát trong mùa nóng.',
            'Hạn chế rủi ro chập cháy do lỗi dây dẫn.',
        ],
        'items' => [
            'Kiểm tra ắc quy, máy phát, dây đai và hệ thống sạc.',
            'Đọc lỗi điện tử và kiểm tra các cầu chì, rơ le.',
            'Kiểm tra gas, máy nén, dàn nóng, dàn lạnh và quạt.',
            'Vệ sinh, thay thế hoặc sửa chữa chi tiết hỏng.',
        ],
        'process' => [
            'Ghi nhận triệu chứng và kiểm tra sơ bộ.',
            'Đo kiểm, đọc lỗi bằng thiết bị chuyên dụng.',
            'Tư vấn phương án xử lý và vật tư cần thay.',
            'Sửa chữa, chạy thử và bàn giao.',
        ],
        'notes' => [
            'Không tự ý thay cầu chì bằng loại có thông số khác.',
            'Hệ thống điện lạnh cần kiểm tra gas và dò rò rỉ trước khi nạp.',
        ],
    ],
    'Dịch vụ lốp và mâm xe' => [
        'description' => 'Kiểm tra, thay thế, cân bằng và xử lý các vấn đề liên quan đến lốp, mâm và độ chụm bánh.',
        'benefits' => [
            'Đảm bảo độ bám đường và độ an toàn khi di chuyển.',
            'Giảm rung lắc và mài mòn lốp không đều.',
            'Kéo dài tuổi thọ lốp và mâm.',
        ],
        'items' => [
            'Kiểm tra tình trạng lốp, vết nứt, độ mòn và áp suất.',
            'Thay lốp, vá lốp hoặc đảo lốp theo nhu cầu.',
            'Cân bằng động và cân chỉnh độ chụm bánh.',
            'Kiểm tra mâm, ốc bánh và cảm biến áp suất nếu có.',
        ],
        'process' => [
            'Kiểm tra tổng thể lốp và mâm.',
            'Tư vấn phương án thay thế hoặc sửa chữa.',
            'Thực hiện cân bằng, cân chỉnh và siết ốc đúng lực.',
            'Kiểm tra lại và bàn giao.',
        ],
        'notes' => [
            'Nên kiểm tra áp suất lốp định kỳ và trước hành trình dài.',
            'Lốp có vết nứt hoặc phồng cần được xử lý ngay.',
        ],
    ],
    'Sơn dặm / sơn vá' => [
        'description' => 'Xử lý các vết trầy xước, bong tróc hoặc hư hỏng nhỏ trên bề mặt sơn, giúp xe gọn gàng và hạn chế gỉ sét.',
        'benefits' => [
            'Khắc phục nhanh các vết xước nhỏ.',
            'Hạn chế ăn mòn kim loại tại vị trí hư hỏng.',
            'Giữ thẩm mỹ tổng thể của xe.',
        ],
        'items' => [
            'Đánh giá mức độ trầy xước và bề mặt cần xử lý.',
            'Vệ sinh, làm phẳng và xử lý chống gỉ nếu cần.',
            'Pha màu, sơn dặm và phủ bóng khu vực sửa chữa.',
            'Đánh bóng và kiểm tra độ đồng màu.',
        ],
        'process' => [
            'Kiểm tra màu sơn và mức độ hư hỏng.',
            'Vệ sinh và xử lý bề mặt.',
            'Sơn dặm, phủ bóng và đánh bóng hoàn thiện.',
            'Kiểm tra cùng khách hàng và bàn giao.',
        ],
        'notes' => [
            'Màu sơn có thể chênh lệch nhẹ do ánh sáng và độ phai theo thời gian.',
            'Không rửa xe mạnh hoặc đánh bóng ngay sau khi sơn.',
        ],
    ],
    'Sơn quây / sơn đổi màu toàn xe' => [
        'description' => 'Sơn lại toàn bộ hoặc một phần thân vỏ, phù hợp khi xe bị hư hỏng nhiều bề mặt hoặc có nhu cầu đổi màu.',
        'benefits' => [
            'Làm mới toàn diện ngoại thất xe.',
            'Xử lý đồng bộ các khu vực hư hỏng hoặc lệch màu.',
            'Có thể lựa chọn phương án sơn theo nhu cầu.',
        ],
        'items' => [
            'Kiểm tra thân vỏ, các vết gò hàn và bề mặt cần xử lý.',
            'Tháo lắp chi tiết, vệ sinh và che chắn khu vực sơn.',
            'Xử lý chống gỉ, sơn lót, sơn màu và phủ bóng.',
            'Đánh bóng, kiểm tra và bàn giao.',
        ],
        'process' => [
            'Tư vấn màu sơn và phạm vi sơn.',
            'Kiểm tra, xử lý bề mặt và các hư hỏng thân vỏ.',
            'Thực hiện sơn theo quy trình nhiều lớp.',
            'Hoàn thiện, kiểm tra chất lượng và bàn giao.',
        ],
        'notes' => [
            'Thời gian thực hiện phụ thuộc diện tích sơn và tình trạng thân vỏ.',
            'Cần thống nhất mã màu và phạm vi sơn trước khi thực hiện.',
        ],
    ],
    'Gò hàn và nắn khung gầm' => [
        'description' => 'Xử lý các hư hỏng kết cấu do va chạm, gò hàn thân vỏ và nắn khung gầm theo tiêu chuẩn an toàn.',
        'benefits' => [
            'Khôi phục hình dáng và kết cấu thân vỏ.',
            'Đảm bảo các chi tiết lắp ghép hoạt động đúng vị trí.',
            'Tạo nền tảng tốt cho sơn và hoàn thiện ngoại thất.',
        ],
        'items' => [
            'Đánh giá mức độ hư hỏng và kết cấu chịu lực.',
            'Gò, hàn, nắn các chi tiết thân vỏ và khung gầm.',
            'Kiểm tra lại khe hở, căn chỉnh và độ cân bằng.',
            'Xử lý chống gỉ và chuyển sang công đoạn sơn nếu cần.',
        ],
        'process' => [
            'Kiểm tra hiện trạng và xác định phạm vi hư hỏng.',
            'Thống nhất phương án gò hàn, nắn chỉnh.',
            'Thực hiện sửa chữa và kiểm tra kết cấu.',
            'Bàn giao hoặc chuyển sang công đoạn sơn hoàn thiện.',
        ],
        'notes' => [
            'Các hư hỏng kết cấu cần được kiểm tra kỹ trước khi sơn.',
            'Thời gian xử lý phụ thuộc mức độ va chạm.',
        ],
    ],
    'Xử lý bảo hiểm thân vỏ' => [
        'description' => 'Hỗ trợ kiểm tra, ghi nhận và xử lý các hư hỏng thân vỏ thuộc phạm vi bảo hiểm theo quy trình.',
        'benefits' => [
            'Giúp khách hàng nắm rõ phạm vi và hồ sơ cần chuẩn bị.',
            'Hạn chế sai sót trong quá trình giám định.',
            'Phối hợp sửa chữa và hoàn thiện sau giám định.',
        ],
        'items' => [
            'Kiểm tra hiện trạng và chụp nhận hư hỏng.',
            'Hướng dẫn hồ sơ, hình ảnh và thông tin cần cung cấp.',
            'Phối hợp với bên bảo hiểm trong quá trình giám định.',
            'Thực hiện sửa chữa theo phạm vi được chấp thuận.',
        ],
        'process' => [
            'Tiếp nhận thông tin và kiểm tra hư hỏng.',
            'Hướng dẫn chuẩn bị hồ sơ bảo hiểm.',
            'Phối hợp giám định và thống nhất phương án.',
            'Sửa chữa, nghiệm thu và bàn giao.',
        ],
        'notes' => [
            'Quyền lợi và phạm vi chi trả do đơn vị bảo hiểm quyết định.',
            'Khách hàng cần cung cấp thông tin trung thực và đầy đủ.',
        ],
    ],
    'Rửa xe và dọn nội thất' => [
        'description' => 'Rửa xe, vệ sinh nội thất và làm sạch các khu vực thường xuyên tiếp xúc để xe sạch sẽ, dễ chịu.',
        'benefits' => [
            'Loại bỏ bụi bẩn, vết bám và mùi khó chịu.',
            'Giữ nội thất sạch và bền màu hơn.',
            'Tạo cảm giác thoải mái khi sử dụng xe.',
        ],
        'items' => [
            'Rửa thân vỏ, mâm, lốp và các khe cửa.',
            'Hút bụi, vệ sinh ghế, thảm và khoang nội thất.',
            'Lau dọn taplo, cửa gió và các bề mặt tiếp xúc.',
            'Làm khô và kiểm tra lại sau khi vệ sinh.',
        ],
        'process' => [
            'Kiểm tra tình trạng bẩn và các khu vực cần lưu ý.',
            'Rửa ngoại thất và vệ sinh nội thất.',
            'Làm khô, kiểm tra và xử lý các vết bẩn còn lại.',
            'Bàn giao xe sạch sẽ.',
        ],
        'notes' => [
            'Thời gian phụ thuộc mức độ bẩn và loại xe.',
            'Thông báo các khu vực nội thất nhạy cảm trước khi vệ sinh.',
        ],
    ],
    'Chăm sóc ngoại thất' => [
        'description' => 'Chăm sóc bề mặt sơn, nhựa, kính và các chi tiết ngoại thất để duy trì độ bóng và hạn chế tác động môi trường.',
        'benefits' => [
            'Giữ bề mặt sơn sáng và hạn chế ố mờ.',
            'Bảo vệ nhựa, cao su và kính khỏi bám bẩn.',
            'Tăng tính thẩm mỹ tổng thể của xe.',
        ],
        'items' => [
            'Vệ sinh bề mặt sơn, kính, nhựa và cao su.',
            'Tẩy các vết bám khó xử lý nếu phù hợp.',
            'Đánh bóng hoặc phủ bảo vệ theo nhu cầu.',
            'Kiểm tra và hoàn thiện các chi tiết ngoại thất.',
        ],
        'process' => [
            'Đánh giá tình trạng bề mặt và lựa chọn phương án.',
            'Vệ sinh và xử lý các vết bám.',
            'Đánh bóng, phủ bảo vệ và hoàn thiện.',
            'Kiểm tra cùng khách hàng và bàn giao.',
        ],
        'notes' => [
            'Không nên đánh bóng mạnh trên bề mặt sơn đã bong tróc.',
            'Hiệu quả phụ thuộc tình trạng sơn thực tế.',
        ],
    ],
    'Bảo vệ và phủ bề mặt' => [
        'description' => 'Phủ lớp bảo vệ lên bề mặt sơn hoặc các chi tiết ngoại thất giúp hạn chế bám bẩn và tác động của môi trường.',
        'benefits' => [
            'Hỗ trợ bảo vệ sơn trước bụi bẩn và nước mưa.',
            'Giúp xe dễ vệ sinh hơn.',
            'Duy trì độ bóng trong thời gian dài hơn.',
        ],
        'items' => [
            'Vệ sinh và xử lý bề mặt trước khi phủ.',
            'Kiểm tra các vết xước, ố mờ cần xử lý.',
            'Thực hiện phủ bảo vệ theo loại sản phẩm.',
            'Kiểm tra độ đồng đều và bàn giao hướng dẫn.',
        ],
        'process' => [
            'Kiểm tra và chuẩn bị bề mặt.',
            'Vệ sinh, sấy khô và xử lý các khuyết điểm.',
            'Phủ lớp bảo vệ và hoàn thiện.',
            'Hướng dẫn cách chăm sóc sau khi phủ.',
        ],
        'notes' => [
            'Hiệu quả và thời gian duy trì phụ thuộc sản phẩm và điều kiện sử dụng.',
            'Cần tuân thủ hướng dẫn rửa xe sau khi phủ.',
        ],
    ],
    'Vệ sinh khoang máy' => [
        'description' => 'Vệ sinh khoang máy đúng cách, hạn chế nước xâm nhập các cụm điện tử và giúp dễ phát hiện rò rỉ.',
        'benefits' => [
            'Giúp quan sát và phát hiện rò rỉ dễ hơn.',
            'Hạn chế bụi bẩn bám lâu ngày trong khoang máy.',
            'Tăng tính thẩm mỹ khi kiểm tra, bảo dưỡng.',
        ],
        'items' => [
            'Che chắn các cụm điện tử và khu vực nhạy cảm.',
            'Vệ sinh bụi bẩn, dầu mỡ và lá cây trong khoang máy.',
            'Làm khô và kiểm tra các đường ống, đầu nối.',
            'Kiểm tra sơ bộ rò rỉ sau khi vệ sinh.',
        ],
        'process' => [
            'Kiểm tra tình trạng khoang máy và các khu vực cần che chắn.',
            'Thực hiện vệ sinh bằng dụng cụ phù hợp.',
            'Sấy khô và kiểm tra lại các đầu nối.',
            'Bàn giao và lưu ý các dấu hiệu cần theo dõi.',
        ],
        'notes' => [
            'Không xịt nước trực tiếp vào các cụm điện tử.',
            'Nếu phát hiện rò rỉ, cần kiểm tra và xử lý trước khi vận hành.',
        ],
    ],
    'Lắp thiết bị an toàn và tiện ích' => [
        'description' => 'Tư vấn và lắp đặt các thiết bị hỗ trợ an toàn, quan sát và tiện ích như camera, cảm biến và màn hình.',
        'benefits' => [
            'Tăng khả năng quan sát và hỗ trợ khi di chuyển.',
            'Bổ sung tiện ích phù hợp với nhu cầu sử dụng.',
            'Đi dây gọn gàng, hạn chế ảnh hưởng nội thất.',
        ],
        'items' => [
            'Tư vấn vị trí lắp đặt và thiết bị phù hợp.',
            'Lắp camera hành trình, camera 360, cảm biến áp suất lốp.',
            'Lắp màn hình Android và các thiết bị tiện ích khác.',
            'Đi dây, đấu nối và kiểm tra hoạt động.',
        ],
        'process' => [
            'Trao đổi nhu cầu và kiểm tra xe.',
            'Thống nhất thiết bị, vị trí lắp đặt và phương án đi dây.',
            'Thực hiện lắp đặt và đấu nối.',
            'Kiểm tra hoạt động, hướng dẫn sử dụng và bàn giao.',
        ],
        'notes' => [
            'Thiết bị và tính năng phụ thuộc sản phẩm khách hàng lựa chọn.',
            'Cần kiểm tra tương thích với hệ thống điện của xe.',
        ],
    ],
    'Nâng cấp ánh sáng và âm thanh' => [
        'description' => 'Nâng cấp hệ thống chiếu sáng và âm thanh theo nhu cầu, đảm bảo phù hợp với xe và điều kiện sử dụng.',
        'benefits' => [
            'Cải thiện khả năng quan sát khi di chuyển.',
            'Nâng cao trải nghiệm âm thanh trong xe.',
            'Có phương án phù hợp với nhu cầu và ngân sách.',
        ],
        'items' => [
            'Tư vấn giải pháp đèn Bi-LED, Laser hoặc nâng cấp ánh sáng.',
            'Tư vấn loa Sub, cách âm và hệ thống âm thanh.',
            'Kiểm tra công suất, đấu nối và độ tương thích.',
            'Lắp đặt, cân chỉnh và kiểm tra hoạt động.',
        ],
        'process' => [
            'Trao đổi nhu cầu và kiểm tra hệ thống hiện tại.',
            'Đề xuất phương án thiết bị và vị trí lắp đặt.',
            'Thực hiện lắp đặt, đấu nối và cân chỉnh.',
            'Kiểm tra, hướng dẫn sử dụng và bàn giao.',
        ],
        'notes' => [
            'Cần tuân thủ quy định về ánh sáng và âm thanh khi tham gia giao thông.',
            'Thiết bị và chi phí phụ thuộc phương án lựa chọn.',
        ],
    ],
    'Cứu hộ giao thông 24/7' => [
        'description' => 'Hỗ trợ cứu hộ khi xe gặp sự cố trên đường, kết nối phương án vận chuyển xe về gara hoặc địa điểm phù hợp.',
        'benefits' => [
            'Hỗ trợ kịp thời khi xe không thể tiếp tục di chuyển.',
            'Giảm rủi ro khi xử lý sự cố trên đường.',
            'Kết nối với gara để kiểm tra, sửa chữa sau cứu hộ.',
        ],
        'items' => [
            'Tiếp nhận thông tin vị trí, tình trạng xe và người trên xe.',
            'Tư vấn phương án cứu hộ phù hợp.',
            'Điều phối xe cứu hộ và vận chuyển xe.',
            'Bàn giao xe tại gara hoặc địa điểm theo thống nhất.',
        ],
        'process' => [
            'Tiếp nhận yêu cầu qua hotline.',
            'Xác minh vị trí và tình trạng xe.',
            'Điều phối cứu hộ và cập nhật tiến trình.',
            'Bàn giao xe và tư vấn bước tiếp theo.',
        ],
        'notes' => [
            'Thời gian tiếp cận phụ thuộc vị trí và điều kiện giao thông.',
            'Cần cung cấp thông tin chính xác để việc hỗ trợ thuận lợi.',
        ],
    ],
    'Giao nhận xe tại nhà' => [
        'description' => 'Hỗ trợ nhận xe tại nhà hoặc địa điểm hẹn và giao lại sau khi hoàn tất dịch vụ theo thống nhất.',
        'benefits' => [
            'Tiết kiệm thời gian đi lại cho khách hàng.',
            'Thuận tiện với lịch làm việc bận rộn.',
            'Theo dõi trạng thái xe qua kênh liên hệ của gara.',
        ],
        'items' => [
            'Tiếp nhận địa chỉ, thời gian và tình trạng xe.',
            'Kiểm tra, ghi nhận hiện trạng khi nhận xe.',
            'Vận chuyển xe về gara và thực hiện dịch vụ.',
            'Giao lại xe và bàn giao thông tin sau dịch vụ.',
        ],
        'process' => [
            'Đặt lịch giao nhận và xác nhận địa điểm.',
            'Nhận xe, ghi nhận hiện trạng và thông tin liên hệ.',
            'Thực hiện dịch vụ theo yêu cầu.',
            'Giao lại xe và xác nhận hoàn tất.',
        ],
        'notes' => [
            'Phạm vi và thời gian giao nhận phụ thuộc địa điểm thực tế.',
            'Khách hàng nên kiểm tra xe cùng nhân viên khi giao nhận.',
        ],
    ],
    'Kiểm tra xe và chẩn đoán lỗi' => [
        'description' => 'Kiểm tra tổng quát hoặc chẩn đoán lỗi theo triệu chứng, sử dụng thiết bị đọc lỗi và kiểm tra trực tiếp.',
        'benefits' => [
            'Xác định đúng nguyên nhân trước khi sửa chữa.',
            'Hạn chế thay thế linh kiện không cần thiết.',
            'Có thông tin rõ ràng để khách hàng quyết định.',
        ],
        'items' => [
            'Ghi nhận triệu chứng và lịch sử sử dụng xe.',
            'Đọc lỗi bằng thiết bị chẩn đoán.',
            'Kiểm tra trực tiếp các hệ thống liên quan.',
            'Tổng hợp kết quả và tư vấn phương án.',
        ],
        'process' => [
            'Tiếp nhận thông tin và triệu chứng.',
            'Kiểm tra, đọc lỗi và xác minh nguyên nhân.',
            'Tư vấn phương án xử lý và mức độ ưu tiên.',
            'Bàn giao kết quả và hẹn lịch sửa chữa nếu cần.',
        ],
        'notes' => [
            'Kết quả chẩn đoán phụ thuộc tình trạng thực tế và thiết bị hỗ trợ.',
            'Một số lỗi cần kiểm tra sâu hoặc chạy thử mới xác định chính xác.',
        ],
    ],
];
