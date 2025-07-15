<?php

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

$fecth_customers_billing_statement = "SELECT customer_code, customer_name  FROM customers WHERE status in ('actived', 'pending', 'liquidating');";
$customers = get_data_by_sql('billing_140', $fecth_customers_billing_statement);
// var_dump($customers);
$users_statements = "SELECT users.user_code, email FROM users INNER JOIN contracts on contracts.user_code = users.user_code";
$users = get_data_by_sql('billing_140', $users_statements);
var_dump($users);