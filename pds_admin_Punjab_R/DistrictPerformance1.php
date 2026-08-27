<?php
require('util/Connection.php');
require('util/SessionCheck.php');
require('Header.php');

function getYearMonth(){
	global $con;
	$query = "SELECT * FROM optimised_table_leg1 ORDER BY last_updated DESC LIMIT 1";
	$result = mysqli_query($con,$query);
	$response = array();
	while($row = mysqli_fetch_array($result))
	{
		$temp = array();
		$temp["year"] = $row["year"];
		$temp["month"] = $row["month"];
		$temp["day"] = $row["day"];
		$temp["id"] = $row["id"];
		$temp["last_updated"] = $row["last_updated"];
		array_push($response,$temp);
	}

	if(count($response) > 0){
		$month_year = strval($response[0]['year'])."_".strval($response[0]['month'])."_".strval($response[0]['day']);
		return $month_year;
	}
	return "";
}


?>
<style>
    td {
            font-size: 15px; /* Increase font size for table headers and data cells */
        }
        .table thead tr th {
    background-color: #95b75d !important;
    /* border: 2px solid #777; */
    color: black;
    /* Optional: Font size for table header */
}
    </style>

                <!-- START BREADCRUMB -->
                <ul class="breadcrumb">
                    <li><a href="#">Home</a></li>
                    <li class="active">District Performance</li>
                </ul>
                <!-- END BREADCRUMB -->


				<!-- PAGE CONTENT WRAPPER -->
                <div class="page-content-wrap">

                    <div class="row">
                        <div class="col-md-12">

                            <!-- START SIMPLE DATATABLE -->
                            <div class="panel panel-default">
							<div class="panel-heading">
                                    <h3 class="panel-title">District Performance</h3>
                                </div>
								<div class="panel-body">
								<div class="row">
									<div class="col-md-4">
									</div>
									<div class="col-md-4">
									</div>
									<div class="col-md-4">
										<div class="form-group">
											<label class="col-md-3 control-label">Month</label>
											<div class="col-md-9">  
												<div class="input-group">
												<span class="input-group-addon"><span class="fa fa-certificate"></span></span>						
												<select class="form-control" id="month" name="month" onchange="fetchDataFromServer()">
													<option value=''>Select</option>
												</select>
												</div>
											</div>
										</div>
									</div>
								</div>
								</br></br>
								<div style="float:right">
									<button id="downloadCSV" class="btn btn-warning" style="margin-bottom: 10px;" type="button">Download CSV</button>
									<button id="downloadXLSX" class="btn btn-success" style="margin-bottom: 10px;" type="button">Download XLSX</button>
								</div>
								</br></br>
							</br></br>
							
                                 <div class="table-responsive">
                                    <table id="export_table" class="table">
                                        <thead>
                                            <tr>
												<th style="font-size:16px">District</th>
												<th style="font-size:16px">Total Tags</th>
												<th style="font-size:16px">Implemented</th>
												<th style="font-size:16px">Not Implemented</th>
												<th style="font-size:16px">District Approved</th>
												<th style="font-size:16px">Not District Approved</th>
												<th style="font-size:16px">Admin Approved</th>
												<th style="font-size:16px">Not Admin Approved</th>
                                            </tr>
                                        </thead>
                                        <tbody>
										<?php
										$month_year = getYearMonth();
										if (!empty($month_year)) {
											$parts = explode("_", $month_year);
											if (count($parts) >= 3) {
												$year = $parts[0];
												$month = $parts[1];
												$day = $parts[2];
												$query = "SELECT id FROM optimised_table_leg1 WHERE year='$year' AND month='$month' AND day='$day'";
											} else {
												$month = $parts[0];
												$year = $parts[1];
												$query = "SELECT id FROM optimised_table_leg1 WHERE month='$month' AND year='$year' ORDER BY last_updated DESC LIMIT 1";
											}
											$result = mysqli_query($con, $query);
											if($result!=NULL && $result!=false){
												$row = mysqli_fetch_assoc($result);
												if($row && isset($row['id'])){
													$query = "SELECT to_district, SUM(CASE WHEN LOWER(TRIM(status)) = 'implemented' THEN 1 ELSE 0 END) AS implemented_count, SUM(CASE WHEN LOWER(TRIM(status)) = 'implemented' THEN 0 ELSE 1 END) AS notimplemented_count, SUM(CASE WHEN LOWER(TRIM(approve_district)) = 'yes' THEN 1 ELSE 0 END) AS district_approved_count, SUM(CASE WHEN LOWER(TRIM(approve_district)) = 'yes' THEN 0 ELSE 1 END) AS notdistrict_approved_count, SUM(CASE WHEN LOWER(TRIM(approve_admin)) = 'yes' THEN 1 ELSE 0 END) AS admin_approved_count, SUM(CASE WHEN LOWER(TRIM(approve_admin)) = 'yes' THEN 0 ELSE 1 END) AS notadmin_approved_count, COUNT(*) AS total_tags FROM optimiseddata_leg1_".$row['id']." GROUP BY to_district;";
													$result = mysqli_query($con,$query);
													if($result!=NULL && $result!=false){
														$numrows = mysqli_num_rows($result);
														if($numrows>0){
															while($row = mysqli_fetch_assoc($result))
															{
																echo "<tr><td>{$row['to_district']}</td>".
																"<td>{$row['total_tags']}</td>".
																"<td>{$row['implemented_count']}</td>".
																"<td>{$row['notimplemented_count']}</td>".
																"<td>{$row['district_approved_count']}</td>".
																"<td>{$row['notdistrict_approved_count']}</td>".
																"<td>{$row['admin_approved_count']}</td>".
																"<td>{$row['notadmin_approved_count']}</td></tr>";
															}
														}
													}
												}
											}
										}
										?>
                                        </tbody>
									<div id="popup" class="popup">
										<a class="close" onclick="hidePopup()" style="font-size:25px">×</a>
										</br></br>
										
										<div class="col-md-6">
										
											<div class="form-group">
                                                <label class="col-md-3 control-label">Username*</label>
                                                <div class="col-md-9">
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><span class="fa fa-info"></span></span>
                                                        <input type="text" class="form-control" id="username" name="username" required />
                                                    </div>
                                                    <span class="help-block">Username</span>
                                                </div>
                                            </div>
											 <input type="hidden" class="form-control" id="deleteid" name="deleteid"  />
											
                                        </div>
                                        <div class="col-md-6">
										
										
											<div class="form-group">
                                                <label class="col-md-3 control-label">Password*</label>
                                                <div class="col-md-9">
                                                    <div class="input-group">
                                                        <span class="input-group-addon"><span class="fa fa-info"></span></span>
                                                        <input type="password" class="form-control" id="password" name="password" required />
                                                    </div>
                                                    <span class="help-block">Password</span>
                                                </div>
                                            </div>
											
											
                                        </div>
										
										<center><button class="btn btn-primary" type="button" onClick="VerifyAndDelete()">Verify</button></center>
									</div>
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

        <!-- START TEMPLATE -->
        
        <!-- END TEMPLATE -->

		<script>
		function post(params,file) {

			method = "post";
			path = file;

			var form = document.createElement("form");
			form.setAttribute("method", method);
			form.setAttribute("action", path);

			for(var key in params) {
				if(params.hasOwnProperty(key)) {
					var hiddenField = document.createElement("input");
					hiddenField.setAttribute("type", "hidden");
					hiddenField.setAttribute("name", key);
					hiddenField.setAttribute("value", params[key]);
					form.appendChild(hiddenField);
				 }
			}

			document.body.appendChild(form);
			form.submit();
		}

		document.getElementById('popup').style.display = 'none';
	
		function hidePopup() {
            document.getElementById('popup').style.display = 'none';
        }
		
		var dataString = "";
		$.ajax({
			type: "POST",
			url: "api/fetchTableDataLeg1.php",
			data: dataString,
			cache: false,
			error: function(){
				alert("timeout");
			},
			timeout: 216000,
			success: function(result){
				try{
					var data = JSON.parse(result);
					var monthYearCombinations = data.map(item => `${item.year}_${item.month}_${item.day}`);
					var dropdown = document.getElementById("month");
					
					  // Clear existing options
					  dropdown.innerHTML = '';

					  // Add new options based on the array
					  monthYearCombinations.forEach(function(item) {
						var option = document.createElement("option");
						option.value = item;
						option.text = item;
						dropdown.add(option);
					  });
				}
				catch (error) {
				}
			}
		});

		function fetchDataFromServer() {
			var month = document.getElementById("month").value;
			if (!month) return;
			$.ajax({
				type: "GET",
				url: "api/DistrictPerformance1.php?format=json&month=" + month,
				cache: false,
				success: function(result){
					try{
						var data = (typeof result === "object") ? result : JSON.parse(result);
						var tbody = document.querySelector("#export_table tbody");
						if (tbody) {
							tbody.innerHTML = "";
							data.forEach(function(row) {
								var tr = document.createElement("tr");
								tr.innerHTML = "<td>" + (row.to_district || '') + "</td>" +
									"<td>" + (row.total_tags || 0) + "</td>" +
									"<td>" + (row.implemented_count || 0) + "</td>" +
									"<td>" + (row.notimplemented_count || 0) + "</td>" +
									"<td>" + (row.district_approved_count || 0) + "</td>" +
									"<td>" + (row.notdistrict_approved_count || 0) + "</td>" +
									"<td>" + (row.admin_approved_count || 0) + "</td>" +
									"<td>" + (row.notadmin_approved_count || 0) + "</td>";
								tbody.appendChild(tr);
							});
						}
					} catch(e) {
						console.error(e);
					}
				}
			});
		}
		
		document.getElementById('downloadCSV').addEventListener('click', async function() {
			try {
				var month = document.getElementById("month").value;
				const csvResponse = await fetch('api/DistrictPerformance1.php?format=csv&month=' + month);
				const csvBlob = await csvResponse.blob();
				downloadFile(csvBlob, 'RolloutPlan_' + getDateString() + '.csv');
			} catch (error) {
				console.error('Error downloading CSV file:', error);
			}
		});

		// Event listener for downloading XLSX
		document.getElementById('downloadXLSX').addEventListener('click', async function() {
			try {
				var month = document.getElementById("month").value;
				const excelResponse = await fetch('api/DistrictPerformance1.php?format=xlsx&month=' + month);
				const excelBlob = await excelResponse.blob();
				downloadFile(excelBlob, 'RolloutPlan_' + getDateString() + '.xlsx');
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
		
		function getDateString(){
			var currentDate = new Date();
			var year = currentDate.getFullYear();
			var month = currentDate.getMonth() + 1; // Month is zero-based, so we add 1
			var day = currentDate.getDate();
			var str = year + "-" + month + "-" + day;
			return str;
		}
			

		</script>
    </body>
</html>
