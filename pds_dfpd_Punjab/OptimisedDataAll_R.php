<?php
require('util/Connection.php');
require('util/SessionCheck.php');
require('Header.php');

?>

 <style>
        /* Increase the font size for the entire page */
        body {
            font-size: 16px; /* Change this value to increase or decrease the base font size */
        }

        /* Increase the font size for specific elements */
        .breadcrumb,
        .panel-title,
        .btn {
            font-size: 12px; /* Adjust the font size for breadcrumbs, panel titles, and buttons */
        }

        /* Increase font size for tables */
        table,
        th,
        td {
            font-size: 15px; /* Font size for table elements */
        }

        /* Increase the font size for form labels, inputs, and buttons */
        label,
        input,
        button {
            font-size: 12px; /* Font size for form elements and buttons */
        }

        /* Increase the font size for specific elements within the page */
        .popup,
        .help-block {
            font-size: 16px; /* Font size for popup elements and help blocks */
        }
    </style>
              
                <!-- START BREADCRUMB -->
                <ul class="breadcrumb">
                    <li><a href="#">Home</a></li>                    
                    <li class="active">All Optimised Data</li>
                </ul>
                <!-- END BREADCRUMB -->                       
                
				
				<!-- PAGE CONTENT WRAPPER -->
                <div class="page-content-wrap">                
                
                    <div class="row">
                        <div class="col-md-12">


                            <!-- START SIMPLE DATATABLE -->
                            <div class="panel panel-default">
								<div class="panel-heading">                                
                                    <h3 class="panel-title">Rice (PC to Mill)</h3> 
                                </div>
								<div class="panel-body">
                                 <div class="table-responsive">
                                    <table id="export_table" class="table">
                                        <thead>
                                            <tr>
                                                <th style="font-size:16px">Date</th>
                                                <th style="font-size:16px">Month</th>
												<th style="font-size:16px">Year</th>
                                                <th style="font-size:16px">PC</th>
                                                <th style="font-size:16px">Mill</th>
                                                <th style="font-size:16px">Milling Center</th>
                                                <th style="font-size:16px">Optimised Data</th>
												<th style="font-size:16px">Generate Data</th>
                                            </tr>
                                        </thead>
                                        <tbody id="table_body">
										<?php
										
										$query = "SELECT * FROM optimised_table WHERE 1";
										$result = mysqli_query($con,$query);
										$numrows = mysqli_num_rows($result);
										while($row = mysqli_fetch_array($result))
										{
											$temp_id = (string)$row['id'];
											echo "<tr><td>{$row['day']}</td>".
											 "<td>{$row['month']}</td>".
											 "<td>{$row['year']}</td>".
											 "<td> <button class='btn btn-info btn-rounded' onclick=\"warehouse_open('{$temp_id}')\">View PCs</button></td>".
             								 "<td> <button class='btn btn-warning btn-rounded' onclick=\"fps_open('{$temp_id}')\">View Mills</button></td>".
             								 "<td> <button class='btn btn-success btn-rounded' onclick=\"milling_center_open('{$temp_id}')\">View Milling Center</button></td>".
             								 "<td> <button class='btn btn-danger btn-rounded' onclick=\"optimised_open('{$temp_id}')\">View Data</button></td>".
											 "<td> <button class='btn btn-danger btn-rounded' onclick=\"generate_report('{$temp_id}')\">View Report</button></td></tr>";
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

        <!-- START TEMPLATE -->      
        
        <!-- END TEMPLATE -->

		<script>
		var methodCalled = "";
		var uidCalled = "";
		function post(params,file) {
			
			method = "post"; 
			path = file;
			
			var form = document.createElement("form");
			form.setAttribute("method", method);
			form.setAttribute("action", path);
			//form.setAttribute("target", "_blank");

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

		function warehouse_open(temp_id){
			post({id:temp_id} ,"PcView_R.php");
		}
		
		function fps_open(temp_id){
			post({id:temp_id} ,"MillView_R.php");
		}
		
		function milling_center_open(temp_id){
			post({id:temp_id} ,"MillingCenter_R.php");
		}
		
		function wheat_pc_open(temp_id){
			post({id:temp_id} ,"../pds_admin_Punjab_W/PcView.php");
		}
		
		function wheat_warehouse_open(temp_id){
			post({id:temp_id,step:"leg1"} ,"../pds_admin_Punjab_W/WarehouseView.php");
		}
		
		function wheat_storage_open(temp_id){
			post({id:temp_id,step:"leg1"} ,"../pds_admin_Punjab_W/StoragePoint.php");
		}
		
		function wheat_weighbridge_open(temp_id){
			post({id:temp_id,step:"leg1"} ,"../pds_admin_Punjab_W/Weighbridge.php");
		}
		
		function wheat_optimised_open(temp_id){
			post({id:temp_id,step:"leg1"} ,"../pds_admin_Punjab_W/OptimisedDataView.php");
		}
		
		function wheat_generate_report(temp_id, leg_id){
			post({id:temp_id,step:"all",legid:leg_id} ,"../pds_admin_Punjab_W/GenerateDataView1.php");
		}

		function optimised_open(temp_id){
			post({id:temp_id} ,"OptimisedDataView_R.php");
		}
		
		function send_email(temp_id){	
			document.getElementById('popup').style.display = 'block';
			uidCalled = temp_id;
		}
		function generate_report(temp_id, leg_id){
			post({id:temp_id,step:"all",legid:leg_id} ,"GenerateDataView_R.php");
		}
		
		function send_all(temp_id){	
			document.getElementById('popup').style.display = 'block';
			uidCalled = temp_id;
		}
		
		function proceed(){
			var username = document.getElementById('username').value;
			var password = document.getElementById('password').value;
			post({username:username,password:password,uid:uidCalled} ,"api/SendEmail.php");
		}
		
		function showPopup() {
            
			var name = document.getElementById('name').value;
            var type = document.getElementById('type').value;
			var latitude = document.getElementById('latitude').value;
            var longitude = document.getElementById('longitude').value;
			var id = document.getElementById('id').value;
            var demand = document.getElementById('demand').value;
            var district = document.getElementById('district').value;

            if (name === '' || type === '' || latitude === '' || longitude === '' || id === '' || demand === '' || district === '') {
                alert('Please enter all fields');
                return false;
            }
			
            document.getElementById('popup').style.display = 'block';
        }
		
		function hidePopup() {
            document.getElementById('popup').style.display = 'none';
        }
		
		
		</script>	
    </body>
</html>
