<?php


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/PHPMailer/src/Exception.php';
require 'vendor/PHPMailer/src/PHPMailer.php';
require 'vendor/PHPMailer/src/SMTP.php';


// Cấu hình timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

$date = date("Y_m_d_H:i");
$month = date("Ym");

// Kết nối DB
$global_array_connection = [
    'billing_140' => [
        'host' => '103.112.209.141',
        'user' => 'billing',
        'pwd'  => 'Admin@diginext2023',
        'db'   => 'billing',
        'port' => 3306,
    ],
    'VoiceReport_140' => [
        'host' => '103.112.209.141',
        'user' => 'billing',
        'pwd'  => 'Admin@diginext2023',
        'db'   => 'VoiceReport',
        'port' => 3306,
    ]
];

function get_data_by_sql($connection, $sql)
{
    global $global_array_connection;
    $conn = $global_array_connection[$connection];

    $connect_server = mysqli_connect($conn['host'], $conn['user'], $conn['pwd'], $conn['db'], $conn['port'])
        or die("[ERROR] Connection failed: " . mysqli_connect_error());

    mysqli_set_charset($connect_server, 'utf8mb4');
    $result = $connect_server->query($sql);

    if (!$result) {
        echo "[ERROR] Query failed: " . $connect_server->error . "\n";
    }

    $array_output = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $array_output[] = $row;
        }
    }

    $connect_server->close();
    return $array_output;
}

// get 

// $contract_code = "00100/2023/DIGISIP/DIGINEXT";

$customer_id = 'DG00000';


// $fecth_customers_billing_statement = "SELECT customer_code, customer_name  FROM customers WHERE status in ('actived', 'pending', 'liquidating');";
$fecth_customers_billing_statement =
    "SELECT customer_code, customer_name, email  
FROM customers
WHERE customer_code in (SELECT customer_code from contracts_details 
WHERE status in ('actived', 'pending', 'liquidating'))";
$customers = get_data_by_sql('billing_140', $fecth_customers_billing_statement);

$contract_customer_user_sql = "SELECT contracts.contract_code, contracts.customer_code, contracts.user_code, customers.customer_name, customers.tax_code, customers.email as customerMail, users.email, contracts.user_name
FROM contracts
INNER JOIN customers ON contracts.customer_code = customers.customer_code
INNER JOIN users ON contracts.user_code = users.user_code
WHERE customers.customer_code = '$customer_id' group by contracts.customer_code
";

$contract_customer_user_result = get_data_by_sql('billing_140', $contract_customer_user_sql);

var_dump($contract_customer_user_result);

function send_mail($emailContact = array())
{
    foreach ($emailContact as $item) {
        // var_dump($item);
        // var_dump($item['customerMail'], $item['customer_name']);
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'dothuylinh270802@gmail.com';                     //SMTP username
            $mail->Password   = 'pusj pvhm efuf vcqk';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('dothuylinh270802@gmail.com', 'Test send Mail');
            // $mail->addAddress($item['customerMail'], $item['customer_name']);     //Add a recipient
            $mail->addAddress('dtlinh270802@gmail.com', 'Hima');     //Add a recipient

            //Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = 'DIGITEL - YÊU CẦU KHÁCH HÀNG CẬP NHẬT ĐỊA CHỈ XUẤT HÓA ĐƠN THEO QUY ĐỊNH MỚI VỀ ĐỊA BÀN HÀNH CHÍNH 2 CẤP';

            $mail->Body = '
            <div style="font-family:Arial, Helvetica, sans-serif; font-size:15px; line-height:1.6; color:#000;">
                <p><strong>Kính gửi Quý Khách hàng ' . $item['customer_name'] . ' , </strong></p>

                <p>Nhằm đảm bảo thông tin hóa đơn điện tử được cập nhật chính xác và phù hợp với quy định mới về địa bàn hành chính 2 cấp, chúng tôi trân trọng đề nghị Quý Khách hàng:</p>

                <p>Nếu doanh nghiệp của Quý vị đã được đồng bộ trên hệ thống hóa đơn điện tử theo địa bàn hành chính 2 cấp, vui lòng gửi email xác nhận để chúng tôi tiến hành cập nhật địa chỉ xuất hóa đơn trên hệ thống.</p>

                <p><strong>Thông tin xác nhận xin gửi về địa chỉ email:</strong><br>
                
                • ' . $item['email'] . ' ( Tên Saler phụ trách: ' . $item['user_name'] . ')' . '<br>

                

                <p><strong>Nội dung email bao gồm:</strong><br>
                • Tên doanh nghiệp: ' . $item['customer_name'] . ' <br>
                • Mã số thuế: ' . $item['tax_code'] . '<br>
                • Địa chỉ xuất hóa đơn mới theo địa bàn hành chính 2 cấp<br>
                • Công văn thông báo thay đổi địa chỉ mới của Quý Công Ty</p>

                <p>Việc cập nhật kịp thời sẽ giúp đảm bảo hóa đơn được phát hành đúng quy định và tránh phát sinh sai sót trong quá trình kê khai, hạch toán.</p>

                <p>Chúng tôi rất mong nhận được sự phối hợp từ Quý Khách hàng.</p>

                <p><strong>Trân trọng cảm ơn!</strong></p>

                <div style="margin-top:30px; padding-top:20px; border-top:1px solid #ccc; font-size:13px; font-family: Arial, sans-serif; color:#444;">
                    <p style="margin: 0; font-weight: bold;">CÔNG TY CỔ PHẦN TẬP ĐOÀN DIGINEXT</p>
                    <p style="margin: 0;">Địa chỉ giao dịch: Lô OF03-19, Tầng 3 - Office, Vinhomes West Point, Đường Phạm Hùng, Phường Mễ Trì, Quận Nam Từ Liêm, Hà Nội.</p>
                    <p style="margin: 0;">Tel: (024-028) 5555 1111 | 19005055</p>
                    <p style="margin: 0;">Website: <a href="https://diginext.com.vn" style="color: #0066cc;">https://diginext.com.vn</a></p>
                    <p style="margin: 0;">Email: <a href="mailto:cskh@diginext.com.vn" style="color: #0066cc;">cskh@diginext.com.vn</a></p>
                </div>

            </div>';

            $mail->AltBody = 'Kính gửi Quý Khách hàng, vui lòng kiểm tra nội dung email bằng trình duyệt hỗ trợ HTML.';


            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}

send_mail($contract_customer_user_result);
