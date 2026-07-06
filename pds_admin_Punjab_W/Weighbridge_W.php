<?php
require('util/Connection.php');
require('util/SessionCheck.php');
require('Header.php');
?>
<script src="crypto-js/crypto-js.js"></script>
<script src="js/Encryption.js"></script>

<style>
        body {
            font-size: 16px;
        }
        .breadcrumb,
        .panel-title,
        .btn {
            font-size: 12px;
        }
        table,
        th,
        td {
            font-size: 15px;
        }
        label,
        input,
        button {
            font-size: 12px;
        }
        .popup,
        .help-block {
            font-size: 16px;
        }
        .table thead tr th {
            background-color: #95b75d !important;
            color: black;
        }
    </style>

                <!-- START BREADCRUMB -->
                <ul class="breadcrumb">
                    <li><a href="#">Home</a></li>
                    <li class="active">Weighbridge</li>
                </ul>
                <!-- END BREADCRUMB -->

				<!-- PAGE CONTENT WRAPPER -->
                <div class="page-content-wrap">

                    <div class="row">
                        <div class="col-md-12">

                            <!-- START SIMPLE DATATABLE -->
                            <div class="panel panel-default">
							<div class="panel-heading">
                                    <h3 class="panel-title">Weighbridge</h3>
                                </div>
								<div style="float:right; margin:10px;">
									<button id="downloadCSV" class="btn btn-warning" style="margin-bottom: 10px;" type="button">Download CSV</button>
									<button id="downloadXLSX" class="btn btn-success" style="margin-bottom: 10px;" type="button">Download XLSX</button>
								</div>
                            
                                <div class="panel-body">
                                 <div class="table-responsive">
                                    <table id="export_table" class="table datatable">
                                        <thead>
                                            <tr>
												<th style="font-size:15px">District</th>
												<th style="font-size:15px">Name</th>
											<th style="font-size:15px">Weighbridge ID</th>
											<th style="font-size:15px">Storage Point</th>
											<th style="font-size:15px">Capacity</th>
											<th style="font-size:15px">Latitude</th>
											<th style="font-size:15px">Longitude</th>
												<th style="font-size:16px">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
										<?php
										
										$query = "SELECT * FROM weighbridge WHERE 1 ORDER BY District";
										$result = mysqli_query($con,$query);
										$numrows = mysqli_num_rows($result);
										while($row = mysqli_fetch_array($result))
										{
											$temp_id = (string)$row['uniqueid'];
											$status = $row['active'];
											if($status==1){
												$status = "<span style='padding:5px' class='btn-success btn-rounded'>Active</span>";
											}
											else{
												$status = "<span style='padding:5px' class='btn-danger btn-rounded'>InActive</span>";
											}
											echo "<tr><td>{$row['District']}</td>".
											"<td>{$row['Name']}</td>".
									"<td>{$row['ID']}</td>".
									"<td>{$row['Storage_Point']}</td>".
									"<td>{$row['Capacity']}</td>".
									"<td>{$row['Latitude']}</td>".
									"<td>{$row['Longitude']}</td>".
											"<td>$status</td></tr>";
										}
										
										?>
                                        </tbody>
                                    </table>
                                  </div>
                                </div>
                            </div>
                            <!-- END SIMPLE DATATABLE -->

                        </div>
                    </div>

                </div>
                <!-- PAGE CONTENT WRAPPER -->
            </div>
            <!-- END PAGE CONTENT -->
        </div>
        <!-- END PAGE CONTAINER -->



    <!-- START SCRIPTS -->
        <!-- START PLUGINS -->
        <script type="text/javascript" src="js/plugins/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="js/plugins/jquery/jquery-ui.min.js"></script>
        <script type="text/javascript" src="js/plugins/bootstrap/bootstrap.min.js"></script>
        <!-- END PLUGINS -->

        <!-- THIS PAGE PLUGINS -->
        <script type='text/javascript' src='js/plugins/icheck/icheck.min.js'></script>
        <script type="text/javascript" src="js/plugins/mcustomscrollbar/jquery.mCustomScrollbar.min.js"></script>
        <script type="text/javascript" src="js/plugins/datatables/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="js/plugins/tableexport/tableExport.js"></script>
		<script type="text/javascript" src="js/plugins/tableexport/jquery.base64.js"></script>
		<script type="text/javascript" src="js/plugins/tableexport/html2canvas.js"></script>
		<script type="text/javascript" src="js/plugins/tableexport/jspdf/libs/sprintf.js"></script>
		<script type="text/javascript" src="js/plugins/tableexport/jspdf/jspdf.js"></script>
		<script type="text/javascript" src="js/plugins/tableexport/jspdf/libs/base64.js"></script>
		
		
        <script type="text/javascript" src="js/plugins.js"></script>
        <script type="text/javascript" src="js/actions.js"></script>
        <!-- END PAGE PLUGINS -->

		<script>
		function getDateString(){
			var currentDate = new Date();
			var year = currentDate.getFullYear();
			var month = currentDate.getMonth() + 1; // Month is zero-based, so we add 1
			var day = currentDate.getDate();
			var str = year + "-" + month + "-" + day;
			return str;
		}
		
		document.getElementById('downloadCSV').addEventListener('click', async function() {
			try {
				const csvResponse = await fetch('api/DownloadOptimalDataWeighbridge.php?format=csv');
				const csvBlob = await csvResponse.blob();
				downloadFile(csvBlob, 'Punjab_Weighbridge_' + getDateString() + '.csv');
			} catch (error) {
				console.error('Error downloading CSV file:', error);
			}
		});

		// Event listener for downloading XLSX
		document.getElementById('downloadXLSX').addEventListener('click', async function() {
			try {
				const excelResponse = await fetch('api/DownloadOptimalDataWeighbridge.php?format=xlsx');
				const excelBlob = await excelResponse.blob();
				downloadFile(excelBlob, 'Punjab_Weighbridge_' + getDateString() + '.xlsx');
			} catch (error) {
				console.error('Error downloading XLSX file:', error);
			}
		});

		function downloadFile(blob, fileName) {
			const url = window.URL.createObjectURL(blob);
			const link = document.createElement('a');
			link.href = url;
			link.download = fileName;
			link.click();
			window.URL.revokeObjectURL(url);
		}
		</script>
    </body>
</html>
