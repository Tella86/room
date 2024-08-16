<?php
session_start();
//error_reporting(0);
include('include/config.php');
include('include/checklogin.php');
check_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt Management</title>
    <link href="http://fonts.googleapis.com/css?family=Lato:300,400,400italic,600,700|Raleway:300,400,500,600,700|Crete+Round:400italic" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendor/fontawesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="vendor/themify-icons/themify-icons.min.css">
    <link href="vendor/animate.css/animate.min.css" rel="stylesheet" media="screen">
    <link href="vendor/perfect-scrollbar/perfect-scrollbar.min.css" rel="stylesheet" media="screen">
    <link href="vendor/switchery/switchery.min.css" rel="stylesheet" media="screen">
    <link href="vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" media="screen">
    <link href="vendor/select2/select2.min.css" rel="stylesheet" media="screen">
    <link href="vendor/bootstrap-datepicker/bootstrap-datepicker3.standalone.min.css" rel="stylesheet" media="screen">
    <link href="vendor/bootstrap-timepicker/bootstrap-timepicker.min.css" rel="stylesheet" media="screen">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/plugins.css">
    <link rel="stylesheet" href="assets/css/themes/theme-1.css" id="skin_color" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" href="../images/healttech.png" type="image/x-icon">

    <style>
    .demo{ font-family: 'Poppins', sans-serif; }
    .panel{
        border-radius: 0;
        border: none;
    }
    .panel .panel-heading{
        background: #00324a;
        padding: 20px 40px;
        border-radius: 0;
        margin: 0 0;
    }
    .panel .panel-heading .title{
        color: #fff;
        font-size: 28px;
        font-weight: 400;
        text-transform: capitalize;
        margin: 0;
    }
    .panel .panel-heading .title span{ font-weight: 600; }
    .panel .panel-heading .radio-inline{
        color: #fff;
        padding: 6px 12px 6px 30px;
        margin: 0 -3px;
        border-radius: 0;
    }
    .panel .panel-heading .radio-inline:first-of-type{ border-radius: 5px 0 0 5px; }
    .panel .panel-heading .radio-inline:last-of-type{ border-radius: 0 5px 5px 0; }
    .panel .panel-body .table{ margin: 0; }
    .panel .panel-body .table tr td{ border-color: #e7e7e7; }
    .panel .panel-body .table thead tr.active th{
        background-color: transparent;
        font-size: 17px;
        font-weight: 600;
        padding: 12px;
        border-top: 1px solid #e7e7e7;
        border-bottom-color: #e7e7e7;
    }
    .panel .panel-body .table tbody tr:hover{ background-color: rgba(0,0,0,0.03); }
    .panel .panel-body .table tbody tr td{
        color: #555;
        font-size: 16px;
        padding: 12px 12px;
        vertical-align: middle;
    }
    .panel .panel-body .table tbody .btn{
        color: #fff;
        background: #37BC9B;
        font-size: 13px;
        padding: 5px 8px;
        border: none;
        border-radius: 2px;
        transition: all 0.3s ease;
    }
    .panel .panel-body .table tbody .btn:hover{ background: #2e9c81; }
    .panel .panel-footer{
        color: #999;
        background-color: transparent;
        padding: 15px;
        border: none;
        border-top: 1px solid #e7e7e7;
    }
    .panel .panel-footer .col{ line-height: 35px; }
    .pagination{ margin: 0; }
    .pagination li a{
        color: #00324a;
        font-size: 15px;
        font-weight: 600;
        text-align: center;
        line-height: 33px;
        height: 35px;
        width: 35px;
        padding: 0;
        display: block;
        transition: all 0.3s ease 0s;
    }
    .pagination li a:hover,
    .pagination li a:focus,
    .pagination li.active a{
        color: #fff;
        background-color: #00324a;
        border-color: #00324a;
    }
    @media only screen and (max-width:767px){
        .panel .panel-heading{ padding: 20px; }
        .panel .panel-heading .title{
            margin: 0 0 10px;
            text-align: center;
        }
        .inline-form{ text-align: center; }
    }
    :root {
            --orange-yellow-crayola: #fbb034; 
        }
    #calendar-popup {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 80%;
        max-width: 500px;
        background: ;
        padding: 15px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        border-radius: 10px;
        z-index: 1000;
    }

    #calendar {
        max-width: 100%;
        margin: 0 auto;
        color: white;
    }

    .fc-event {
        background-color: #007BFF;
        border: none;
        color: #fff;
        font-size: 0.9em;
    }

    #calendar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }

    #close-calendar {
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer;
        font-size: 18px;
        color: #333;
    }

    #calendar-popup h1 {
        font-size: 1.5em;
        margin-bottom: 15px;
        color: var(--orange-yellow-crayola);
    }
    #backToTopBtn {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 30px;
        z-index: 99;
        border: none;
        outline: none;
        background-color: #007bff;
        color: white;
        cursor: pointer; 
        padding: 15px; 
        border-radius: 10px; 
        font-size: 18px; 
    }
    #backToTopBtn:hover {
        background-color: #ac40ab;
    }
    strong {
        font-weight: bold;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        font-size: 18px;
        text-align: left;
    }
    th, td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
    }
    th {
        background-color: #f4f4f4;
    }
    button {
        padding: 5px 10px;
        margin: 2px;
        border: none;
        color: #fff;
        cursor: pointer;
    }
    .view-button {
        background-color: #4CAF50; /* Green */
    }
    .print-button {
        background-color: #008CBA; /* Blue */
    }
    </style>
</head>
<body>
    <div id="app">
        <?php include('include/sidebar.php');?>
        <div class="app-content">
            <?php include('include/header.php');?>
            <div class="main-content">
                <div class="wrap-content container" id="container">
                    <!-- start: PAGE TITLE -->
                    <section id="page-title">
                        <div class="row">
                            <div class="col-sm-8">
                                <h1 class="mainTitle">Patient | Dashboard</h1>
                            </div>
                            <ol class="breadcrumb">
                                <li><span>User</span></li>
                                <li class="active"><span>Dashboard</span></li>
                            </ol>
                        </div>
                    </section>
                    <!-- end: PAGE TITLE -->

                    <!-- start: PAGE CONTENT -->
                    <div class="container">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title">Receipt Management</h3>
                                    </div>
                                    <div class="panel-body">
                                        <table class="table table-striped table-bordered table-hover" id="receipts-table">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Transaction ID</th>
                                                    <th>Phone</th>
                                                    <th>Amount</th>
                                                    <th>Description</th>
                                                    <th>Date</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Rows will be injected here by JavaScript -->
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="panel-footer">
                                        <div class="row">
                                            <div class="col col-xs-4">Page <span id="current-page">1</span> of <span id="total-pages">1</span></div>
                                            <div class="col-xs-8">
                                                <ul class="pagination hidden-xs pull-right" id="pagination">
                                                    <!-- Pagination buttons will be injected here -->
                                                </ul>
                                                <ul class="pagination visible-xs pull-right" id="pagination-xs">
                                                    <!-- Pagination buttons for mobile will be injected here -->
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end: PAGE CONTENT -->
                </div>
            </div>
        </div>
    </div>

            <!-- start: FOOTER -->
            <?php include('include/footer.php');?>
            <!-- end: FOOTER -->
            <div class="clear"> </div>
        </div>
        <button id="backToTopBtn" title="Go to top">&#8679;</button>

        <!-- start: SETTINGS -->
        <?php include('include/setting.php');?>
        <!-- end: SETTINGS -->

        <!-- start: MAIN JAVASCRIPTS -->
        <script src="vendor/jquery/jquery.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.min.js"></script>
        <script src="vendor/modernizr/modernizr.js"></script>
        <script src="vendor/jquery-cookie/jquery.cookie.js"></script>
        <script src="vendor/perfect-scrollbar/perfect-scrollbar.min.js"></script>
        <script src="vendor/switchery/switchery.min.js"></script>
        <!-- end: MAIN JAVASCRIPTS -->
        <!-- start: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
        <script src="vendor/maskedinput/jquery.maskedinput.min.js"></script>
        <script src="vendor/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="vendor/autosize/autosize.min.js"></script>
        <script src="vendor/selectFx/classie.js"></script>
        <script src="vendor/selectFx/selectFx.js"></script>
        <script src="vendor/select2/select2.min.js"></script>
        <script src="vendor/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
        <script src="vendor/bootstrap-timepicker/bootstrap-timepicker.min.js"></script>
        <!-- end: JAVASCRIPTS REQUIRED FOR THIS PAGE ONLY -->
        <!-- start: CLIP-TWO JAVASCRIPTS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentPage = 1;
        let totalPages = 1;

        function fetchReceipts(page = 1) {
            fetch(`get_receipts.php?page=${page}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        totalPages = data.totalPages;
                        populateTable(data.receipts);
                        updatePagination();
                    } else {
                        alert('Failed to fetch receipts');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while fetching receipts');
                });
        }

        function populateTable(receipts) {
            const tableBody = document.querySelector('#receipts-table tbody');
            tableBody.innerHTML = '';
            receipts.forEach((receipt, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${(currentPage - 1) * 5 + index + 1}</td>
                    <td>${receipt.transaction_id}</td>
                    <td>${receipt.phone}</td>
                    <td>KES ${parseFloat(receipt.amount).toFixed(2)}</td>
                    <td>${receipt.description}</td>
                    <td>${receipt.date}</td>
                    <td>
                        <button class="view-button" onclick="viewReceipt('${receipt.file_path}')">View</button>
                        <button class="print-button" onclick="printReceipt('${receipt.file_path}')">Print</button>
                    </td>
                `;
                tableBody.appendChild(row);
            });
        }

        function updatePagination() {
            const pagination = document.getElementById('pagination');
            const paginationXs = document.getElementById('pagination-xs');
            const currentPageElem = document.getElementById('current-page');
            const totalPagesElem = document.getElementById('total-pages');
            pagination.innerHTML = '';
            paginationXs.innerHTML = '';
            currentPageElem.textContent = currentPage;
            totalPagesElem.textContent = totalPages;

            for (let i = 1; i <= totalPages; i++) {
                const li = document.createElement('li');
                li.innerHTML = `<a href="#" data-page="${i}">${i}</a>`;
                if (i === currentPage) {
                    li.classList.add('active');
                }
                li.addEventListener('click', function(event) {
                    event.preventDefault();
                    currentPage = i;
                    fetchReceipts(currentPage);
                });
                pagination.appendChild(li);

                const liXs = document.createElement('li');
                liXs.innerHTML = `<a href="#" data-page="${i}">${i}</a>`;
                if (i === currentPage) {
                    liXs.classList.add('active');
                }
                liXs.addEventListener('click', function(event) {
                    event.preventDefault();
                    currentPage = i;
                    fetchReceipts(currentPage);
                });
                paginationXs.appendChild(liXs);
            }
        }

        window.viewReceipt = function(filePath) {
            window.open(filePath, '_blank');
        }

        window.printReceipt = function(filePath) {
            const printWindow = window.open(filePath, '_blank');
            printWindow.print();
        }

        fetchReceipts();
    });
    </script>
</body>
</html>
