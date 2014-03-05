<?php

/* ---------------------------------------------------------------- */
/*                                                                  */
/*   Demo PHP Grid Data Script                                      */
/*                                                                  */
/* ---------------------------------------------------------------- */



// Connect to Database
//   In this case MySQL TESTDB

$dblink = @mysql_connect("localhost", "[database username]","[database password]");
$success = @mysql_select_db("TESTDB",$dblink);



// Read the ProductInfo Table
//    ProductInfo table definition
//       ProdName       VARCHAR
//       ProdDesc       VARCHAR
//       ProdStdPrice   REAL
//       ProdProPrice   REAL
//       ProdEntPrice   REAL
 $result = "";
 $query  = "SELECT * FROM ProductInfo";
 $result = mysql_query($query);

 while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

    $ProdName      = $row["ProdName"];
    $ProdDesc      = $row["ProdDesc"];
    $ProdStdPrice  = $row["ProdStdPrice"];
    $ProdProPrice  = $row["ProdProPrice"];
    $ProdEntPrice  = $row["ProdEntPrice"];

    // Output each row as a comma seperated row for the data grid
    print $ProdName.",".$ProdDesc.",".$ProdStdPrice.",".$ProdProPrice.",".$ProdEntPrice."\n";

 }


// Log off the database
mysql_close($dblink);


?>