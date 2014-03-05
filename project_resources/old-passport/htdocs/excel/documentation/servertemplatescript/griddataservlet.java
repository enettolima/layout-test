import java.sql.*;
import java.lang.*;
import java.io.*;
import javax.servlet.*;
import javax.servlet.http.*;

public class GridDataServlet extends HttpServlet {

    // This is a simple Java Servlet demonstrating how a
    // server side script can be used to connect the
    // Data Grid to a Database.
    // Although this is a Java Servlet the same method
    // can used in any server side scripting language
    // connecting to any database.
    // e.g. ASP, PHP, CGI

    // Initialise and set variables
	String url="jdbc:MySQL:///TESTDB";  // URL specifying the JDBC connection to a MySQL database TESTDB.
	Connection con = null;              // Database connection object
	Statement stmt = null;              // Statement String
	String query;                       // Query String


//-----------------------------------------------------------------------------

	public void doGet(HttpServletRequest req, HttpServletResponse res)
		   throws ServletException, IOException {

        // Set the output characterics for the return data
        //   Rather than returning an html page here we wish to return
        //   the data to the Data Grid in Plain Text format
        res.setContentType("text/html");
		ServletOutputStream out = res.getOutputStream();

        // Establish the database connection
		try {
			// Connect to TESTDB
			Class.forName("org.gjt.mm.mysql.Driver");
			con = DriverManager.getConnection (url,"[database username]","[database password]");
           	stmt = con.createStatement();

            // Build the query statement and retrieve the database records
            query = "SELECT * FROM ProductInfo";
            ResultSet srs = stmt.executeQuery(query);

            // Process the database records and return the Data
            //   The Method GridData reads through the database
            //   records and formats the data for the Data Grid Applet
            out.println(GridData(srs));

		} // End try


        // Error handling
		catch(ClassNotFoundException e) {out.println("Could not load database driver: " + e.getMessage());}
		catch(SQLException e) {out.println("SQLException caught: " + e.getMessage());}

        // All finished so close the database connection
		finally {
				 try {if (con != null) con.close();}
			     catch (SQLException e) {}
        }




						   } // End doGet
//-----------------------------------------------------------------------------


    public void doPost(HttpServletRequest request,HttpServletResponse response)
		        throws ServletException, IOException {doGet(request, response);}

//-----------------------------------------------------------------------------

	public static String GridData(ResultSet srs) {

		String rsltStr = "";
		String productname = null;
		String standardcost;
		String professionalcost;
		String launchdate;
		String category;

		// Read through the records and construct the return string
        // in the correct format for the Data Grid Applet
		try {

 		  while (srs.next()) {

            // Set default values
		    productname      = " ";
		    standardcost     = " ";
		    professionalcost = " ";
		    launchdate       = " ";
		    category         = " ";

            // Extract the values from the database record
			productname = srs.getString("productname");
			standardcost = srs.getString("standardcost");
			professionalcost = srs.getString("professionalcost");
			launchdate = srs.getString("launchdate");
			category = srs.getString("category");

            // Build the return info
            // In this case we want the data in comma seperated
            // format.
            rsltStr = rsltStr +
                      productname + "," +
                      standardcost + "," +
                      professionalcost + "," +
                      launchdate + "," +
                      category + "\n";


		  } // end while


		} // End try


        // Error handling
		catch(SQLException e) {rsltStr = rsltStr + "\nSQLException caught: " + e.getMessage();}


         return(rsltStr);
				   	       }


//-----------------------------------------------------------------------------------

} // End class