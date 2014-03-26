var salesTrackerVersion = '4.03';
var sqlServerConnection = 'jdbc:sqlserver://63.99.149.10:6359';
var sqlServerReadUser   = 'ebt4051v3r';
var sqlServerReadPass   = '3su9pJGNVPf2jQ6jte7vTp4H';
var sqlServerWriteUser  = 'ebt4051v3w';
var sqlServerWritePass  = 'rf4wdyThCt7jGH7QCq4m5PHr';
var devModeOn = false;

/**
 * Using BetterLogger for handier loging
 */
Logger = BetterLog.useSpreadsheet();


var errorRewriteMap = {
  '*******From**********' : '***********To*********',
  'Cannot read property "0" from null.' : 'Error inputting schedule!'
}



function createBudget()
{
  logApplicationAccess_();

  var versionDisable = [];
  versionDisable = loadVersionFromSQL_();
  
  if (! devModeOn && salesTrackerVersion != versionDisable[0])
  {
    Browser.msgBox(
      "Please update your sales tracker! The current version is: " + versionDisable[0] + ". " + 
      "Your version is " + salesTrackerVersion + ". " +
      "The newest version can be found on your google drive under \"Shared with Me\", make sure to Make a Copy!"
    );
  }
  
  if (versionDisable[1] == "Y")
  {
    Browser.msgBox("This version of the Sales Tracker has been disabled! Please update to the newest version.")
    return;
  }
  
  errorHandling_();
}

function onOpen(e){
  //Logger.log('onOpen fired');
  launchStoreHoursOverrides_();
}


/**
 * Creates entry in database logging execution info for this
 */
function logApplicationAccess_(){
  var conn = Jdbc.getConnection(sqlServerConnection, sqlServerWriteUser, sqlServerWritePass);
  var stmt = conn.createStatement();
  var ret = stmt.execute("USE EBTGOOGLE;");
  
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName("EmployeeHours");
  var temp = sheet.getRange("C1");
  var storeNumber = temp.getValue();
  var temp = sheet.getRange("C2");
  var fromDate = Utilities.formatDate(temp.getValue(),"GMT","MM/dd/yyyy");
  
  var rs = stmt.execute("INSERT INTO GoogleDoc_LOG (Code,Date,Application,Notes) VALUES (N'" + storeNumber + "',CURRENT_TIMESTAMP,'Sales Tracker V" + salesTrackerVersion + "','" + fromDate + "')");
  
  stmt.close();
  conn.close();
}


function errorHandling_()
{
  try 
  {
    launch_();
  } 
  catch (e) 
  {
   
    Browser.msgBox(translateError_(e.message));
    
    var itEmail;
    
    itEmail = Browser.msgBox("Do you wish to email IT regarding this error?", Browser.Buttons.YES_NO);
    if (itEmail == "yes")
    {
      var ss = SpreadsheetApp.getActiveSpreadsheet();
      var sheet = ss.getSheetByName("EmployeeHours");
      var temp1 = sheet.getRange("C1");
      var name = temp1.getValue();
      var email = "isaac@earthboundtrading.com";

      //add more error messages to display to the user
      MailApp.sendEmail(email, "Error Report from store " + name, e.message);
    }
    else
    {
    }
  }
}

function launch_() 
{

  // Populate 'Targets' & 'Actuals' tables from SQL 
  loadDatafromSql_();
  
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName("EmployeeHours");

  /** 
   * Get array of data with full names & inout pairs
   */
  var employeeDataRange = ss.getRangeByName("employeeData");
  var employeeObjects = getRowsData_(sheet, employeeDataRange);
  

  // POPULATE array of the EmpCodes
  var empCode = ss.getRangeByName("EmployeeCode");
  // empCodes: [["301CY", "301TE", "", "", "", "", "", "", "", "", "", "", "", "", ""]]
  var empCodes = arrayTranspose_(empCode.getValues());

  // POPULATE array of the inout pairs which were input by the manager
  var inputRange = ss.getRangeByName("inputCheckRange");
  /*
  input: [
      [(new Date(-2209100400000)), (new Date(-2209071600000)), "", ""], 
      [(new Date(-2209100400000)), (new Date(-2209068000000)), "", ""], 
      ["", ""],
      ["", ""]
  */
  input = areaReadRow_(inputRange.getValues());

  // POPULATE array from entire actuals sheet
  var sheet = ss.getSheetByName("Actuals");
  var actualData = ss.getRangeByName("ActualRange");
  /*
  actualArray: [
    ["301", "301", "301", "301", "301", "301", "301", "301", "301", "301", ...],
    [395.86, -22.46, 262.92, 256.8, 312.67, 138.16, 333.29, 152.99, 295.87,...],
    [2, 2, 2, 2, 3, 3, 3, 4, 4, 4, 5, 5, 5, 6, 6, 6, 6, 7, 7, 7, 7, 7, 1, 1...],
    ["301CY", "301JP", "301SP", "301TB", "301CY", "301JP", "301TE", "301SP"...],
    [(new Date(1382322803000)), (new Date(1382322803000)), (new Date(138232...],
    ["10/07/2013", "10/07/2013", "10/07/2013", "10/07/2013", "10/08/2013", ...]
  ]
  */
  var actualArray = arrayTranspose_(actualData.getValues());


  // POPULATE array from the Targets sheet
  var sheet = ss.getSheetByName("Targets");
  var targetData = ss.getRangeByName("TargetRange");
  /* 
  dataArray: [
    [301, 301, 301, 301, 301, 301, 301, 301, 301, 301, 301, 301, 301, 301, ...],
    [869.548106760314, 869.548106760314, 869.548106760314, 869.548106760314...],
    [2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 3, 4, 4...],
    ["LY13W301 ", "LY13W301 ", "LY13W301 ", "LY13W301 ", "LY13W301 ", "LY13...],
    [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 10, 11, 12, 13, 14, 15, 16...],
    [0.016838, 0.056343, 0.109398, 0.086129, 0.079418, 0.118307, 0.082297, ...],
    [14.6414510216302, 48.9929489791964, 95.1268237833648, 74.8933088871591...],
    [(new Date(1381129200000)), (new Date(1381129200000)), (new Date(138112...],
    [9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9, 9...],
    [21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21, 21...],
  ]
  */
  var dataArray = arrayTranspose_(targetData.getValues());
  
  var sheet = ss.getSheetByName("Results");
  var summaryDataRange = ss.getRangeByName("SummaryHour");
  var summaryObjects = getColumnsData_(sheet, summaryDataRange);  
  var headerHeight = summaryDataRange.getRowIndex();
  var height = summaryDataRange.getNumRows();

  // Create array of empty employee objects from the empCodes
  var employee = filterEmpCode_(empCodes);
  
  // Validate datetime input from manager (which just look like 9:00AM, but eval out to datetime)
  checkInput_(input, employee);

  // Log store's supplied schedule in the SCHED_FROM_STORE table back home 
  // (not actually doing anything with this)
  scheduleFromStore_(input,employee);

  var storeTarget = [];
  /*
    storeTarget is an array of days with each day being an array of half-hour sales
    goals. I'm not totally clear on how it is derived because there is a lot of 
    juggling that has to happen since we're in Google Spreadsheet world that we
    won't really have to deal with in a more classic data structure. So it's probably
    only important to know what it is.
  storeTarget: 
  [
    [0, 0, ... 0, 0, 7.3207255108151, 7.3207255108151, 24.4964744895982, ... ],
    [0, 0, ... 0, 0, 10.26209800991535, 10.26209800991535, 15.4853720273 ... ],
    [0, 0, ... 0, 0, 9.01034308565265, 9.01034308565265, 36.247661077874 ... ],
    [0, 0, ... 0, 0, 24.8239318982533, 24.8239318982533, 14.676862598701 ... ],
    [0, 0, ... 0, 0, 15.19015642045825, 15.19015642045825, 40.6191228957 ... ],
    [0, 0, ... 0.2064225620698135, 0.2064225620698135, 23.6412055318213, ... ],
    [0, 0, ... 0, 0, 0, 0, 34.70636731583765, 34.70636731583765, 46.5498 ... ]
  ]
  */

  /**
  * in addition to returning the storeTarget data structure, assignTarget also
  * populates the Results sheet.
  */
  storeTarget = assignTarget_(dataArray, height, headerHeight);
  
  employee = actualSalesPerEmployee_(actualArray, employee);

  getEmployeeAmount_(employeeObjects, height, headerHeight, storeTarget, employee);

}

function translateError_(stringError)
{
  if (errorRewriteMap[stringError]) {
    return errorRewriteMap[stringError];
  } else {
    return stringError;
  }
}

/**
 * Populate...
 * 
 * Sheet "Targets" from the contents of EBTGOOGLE.SCHED_BUDGET_PER_HOURS_FINAL_TABLE, which I believe is the table Marcelo
 * creates from his Access tool.
 *
 * Sheet "Actuals" from the contents of EBTGOOGLE.SCHED_SALES_BY_EMPLOYEE, which I believe is populated from Retail Pro
 */
function loadDatafromSql_() {
  
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheetEmployeeHours = ss.getSheetByName("EmployeeHours");
  var storeNumber = sheetEmployeeHours.getRange("C1").getValue();
  var fromDate = Utilities.formatDate(sheetEmployeeHours.getRange("C2").getValue(),"GMT","MM/dd/yyyy");
  var toDate = Utilities.formatDate(sheetEmployeeHours.getRange("C3").getValue(),"GMT","MM/dd/yyyy");
  var conn = Jdbc.getConnection(sqlServerConnection, sqlServerReadUser, sqlServerReadPass);
  var stmt = conn.createStatement();
  
  stmt.setMaxRows(200);
  
  stmt.execute("USE EBTGOOGLE;");

  var queryTargets = 
      "SELECT "+
        "Store, "+
        "DailyBudget, "+
        "BDWeekday, "+
        "HR_PROFILE, "+
        "PROF_HOUR_NEW, "+ 
        "PROF_PER, "+ 
        "HR_BUDGET, "+
        "Date, "+
        "HR_OPEN_MIL, "+
        "HR_CLOSE_MIL "+
      "FROM "+
        "SCHED_BUDGET_PER_HOURS_FINAL_TABLE "+
      "WHERE "+
        "Store = '"+storeNumber+"' and "+
        "Date >= convert(datetime, '"+fromDate+"', 101) and "+
        "Date <= convert(datetime, '"+toDate+"', 101) "+
      "ORDER BY "+
        "Store, "+
        "Date, "+
        "PROF_HOUR_NEW";
  
  var rsTargets = stmt.executeQuery(queryTargets);
  
  var sheetTargets = ss.getSheetByName("Targets");
  sheetTargets.clear();
  
  var cell = sheetTargets.getRange('A1');
  var row = 0;
  var NFields = 10;
  
  while(rsTargets.next()) {
    for (var i = 0; i < NFields; ++i){
      cell.offset(row, i).setValue(rsTargets.getString(i + 1)); //col1 
    }
    row++;
  }
  rsTargets.close();
  
  var queryActuals = 
    "SELECT "+
      "CODE, "+ 
      "EXT_PRICE, "+ 
      "DAYWK,EMPL_NAME, "+
      "LastPollTime, "+ 
      "DATE "+ 
    "FROM "+ 
      "SCHED_SALES_BY_EMPLOYEE "+ 
    "WHERE "+ 
      "CODE = '" + storeNumber + "' and "+ 
      "date >= convert(datetime, '" + fromDate + "', 101) and "+ 
      "date <= convert(datetime, '" + toDate + "', 101) "+ 
    "ORDER BY "+ 
      "CODE, "+ 
     "convert(datetime,Date,101), "+
      "EMPL_NAME";
  
Logger.log(queryActuals)
      
  var rsActuals = stmt.executeQuery(queryActuals);
   
  var sheetActuals = ss.getSheetByName("Actuals");
  
  sheetActuals.clear();
  
  var cell = sheetActuals.getRange('A1');
  var row = 0;
  var NFields = 6;
  
  while(rsActuals.next()) {
    for (var i = 0; i < NFields; ++i)
    {
      cell.offset(row, i).setValue(rsActuals.getString(i + 1)); //col1 
    }
    row++;
  }
  
  rsActuals.close();
  stmt.close();
  conn.close();
}



function areaReadRow_(data) 
{
  if (data.length == 0 || data[0].length == 0) 
  {
    return null;
  }
  
  var ret = [];
  for (var i = 0; i < data[0].length; ++i) 
  {
    ret.push([]);
  }
  
  for (var i = 0; i < data.length; ++i) 
  {
    for (var j = 0; j < data[i].length; ++j) 
    {
      ret[i][j] = data[i][j];
    }
  }
  return ret;
}


/**
 * Record the schedule the store input into the SCHED_FROM_STORE table
 * (that we actually aren't doing anything with right now)
 */
function scheduleFromStore_(input, employee)
{
  var conn = Jdbc.getConnection(sqlServerConnection, sqlServerReadUser, sqlServerReadPass);
  var stmt = conn.createStatement();
  var ret = stmt.execute("USE EBTGOOGLE;");

  // Populate 'logTime' from SQLSERVER time. We will use this time as an index.
  var serverTime = stmt.executeQuery("Select getDate()");
  serverTime.next();
  var logTime = serverTime.getString(1);
  serverTime.close();
  stmt.close();
  conn.close();
  
  var conn = Jdbc.getConnection(sqlServerConnection, sqlServerWriteUser, sqlServerWritePass);
  var stmt = conn.createStatement();
  var ret = stmt.execute("USE EBTGOOGLE;");
  
  // Logger.log(logTime);
  
  // Get store # & from date from EmployeeHours
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName("EmployeeHours");
  var storeNumber = sheet.getRange("C1").getValue();
  var fromDate = Utilities.formatDate(sheet.getRange("C2").getValue(),"GMT","MM/dd/yyyy");
  
  // For each employee...
  for (var i = 0; i < employee.length; i++)
  {
    var code = employee[i].empCode;

    // For each "in" field...
    for (var j = 0; j < input[i].length; j = (j+2))
    {
      // If it's not empty....
      if (input[i][j] != "")
      {
        /*
        
        Insert a row in SCHED_FROM_STORE using a query like the following:
        
        query:
        
        INSERT INTO SCHED_FROM_STORE (
            Created_Date, 
            Store_Code, 
            Date_Start, 
            Emp_Code, 
            In_Time, 
            Out_Time
        ) VALUES (
            CONVERT(DATETIME,'2013-10-21 11:34:05.137', 102), 
            N'301', 
            CONVERT(DATETIME, '10/07/2013 00:00:00', 102), 
            N'301TE', 
            CONVERT(DATETIME, '10/07/2013 09:00', 102) + 0, 
            CONVERT(DATETIME,'10/07/2013 18:00', 102) + 0
        )
        
        SCHED_FROM_STORE looks like:
        
        Oct 21 2013 11:46:52:550AM 301        Nov  3 2013 12:00:00:000AM 311MM    Nov  8 2013 03:30:00:000PM Nov  8 2013 05:30:00:000PM
        Oct 21 2013 11:46:52:550AM 301        Nov  3 2013 12:00:00:000AM 311MM    Nov  8 2013 09:00:00:000AM Nov  8 2013 03:00:00:000PM
        Oct 21 2013 11:46:52:550AM 301        Nov  3 2013 12:00:00:000AM 311MM    Nov  7 2013 03:30:00:000PM Nov  7 2013 05:30:00:000PM
        
        */

        var query = "INSERT INTO SCHED_FROM_STORE (Created_Date, Store_Code, Date_Start, Emp_Code, In_Time, Out_Time) VALUES (CONVERT(DATETIME,'" + logTime + "', 102), N'" + storeNumber + "', CONVERT(DATETIME, '" + fromDate + " 00:00:00', 102), N'" + code + "', CONVERT(DATETIME, '" + fromDate + " " + militaryHours_(decimalHours_(input[i][j], 0)) + "', 102) + " + Math.floor(j/4) + ", CONVERT(DATETIME,'" + fromDate + " " + militaryHours_(decimalHours_(input[i][j+1]), 0) + "', 102) + " + Math.floor(j/4) + ")";
        var rs = stmt.execute(query);
      }
    }
  }
  
  stmt.close();
  conn.close();
}

function checkInput_(input)
{
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName("EmployeeHours");
  
  var columnName = ["D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", 
                    "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z", "AA", "AB", "AC", "AD", "AE"];

  // Test for ^"Sat Dec 03 2"...
  var regEx = /[A-Za-z]{3}\s[A-Za-z]{3}\s[0-9]{2}\s[0-2]/gi;

  for (var i = 0; i < input.length; i++)
  {
    for (var j = 0; j < input[i].length; j++)
    {
      // If the column is a non-empty string, make sure it passes regex for datetime above
      if (!regEx.test(input[i][j]) && (typeof(input[i][j]) == "string" && input[i][j] != ""))
      {
        Browser.msgBox("Input error " + input[i][j] + " found at: " + columnName[j] + (i+6)); 
        sheet.getRange(columnName[j] + (i+6)).activate();
      }
    }
  }
}

function individualTarget_(employeeObjects, storeTarget, groupPerHour, indPerHour, height, headerHeight, employee)
{
  /* 
  ///////////////////////////////////////////////////////////////////////////////
  Number of individuals ---> What hours they worked 5 * 7 * 48                 //
  Number of days ---> What hours they worked 7 * 48                            //
  Number of days ---> What the stores target is 7 * 48                         //
  Individual Target 7 * 48                                                     //
  ///////////////////////////////////////////////////////////////////////////////
  */
  var timeIntervalAmount = 48;
  var individualTarget = createArray_(storeTarget.length, timeIntervalAmount);
  
  for (var i = 0; i < storeTarget.length; i++)
  {
    for (var j = 0; j < storeTarget[i].length; j++)
    {
      if (groupPerHour[i][j] == 0)
      {
        individualTarget[i][j] = 0;
      }
      else
      {
        individualTarget[i][j] = (storeTarget[i][j] / groupPerHour[i][j]);
      }
      if (individualTarget[i][j] == null)
      {
        individualTarget[i][j] = 0;
      }
    }
  }
  
  var columnValue = ["D", "H", "L", "P", "T", "X", "AB"];
  var sheetName = "Results";
  
  for (var a = 0; a < individualTarget.length; a++)
  {
    columnWrite_(sheetName, columnValue[a], individualTarget[a], height, headerHeight);
  }
  
  employeeWrite_(employeeObjects, indPerHour, individualTarget, timeIntervalAmount, employee);
}


/**
 * @param input array empCodes [["301CY", "301TE", "", "", "", "", "", "", "", "", "", "", "", "", ""]]
 * @param output array of initialized employee objects that look like...
 * 
 {
  "empCode": "301CY",
  "totalSale": 0.0,
  "weekSale": [0,0,0,0,0,0,0],
  "target" :  [0,0,0,0,0,0,0],
  "difference" : [0,0,0,0,0,0,0],
  "totalTarget" : 0.0,
  "totalDifference" : 0.0
 }
 */
function filterEmpCode_(empCodes)
{
  var filteredCode = [];
  var codes = [];
  var employee = [];
  var index = 0;
  
  for (var i = 0; i < empCodes[0].length; i++)
  {
    codes[i] = empCodes[0][i].match(/^(\w+)/);
    if (codes[i] != null)
    {
      filteredCode.push(codes[i].slice(0, 1));
    }
  }  
  
  for (var j = 0; j < filteredCode.length; j++)
  {
    employee[j] = new employeeCreate_(filteredCode[j]);
  }
  
  return employee;
}

function filterStorePerDay_(totalByDayArray, store)
{
  for (var i = 0; i < totalByDayArray.length; i++)
  {
    var regex = /\d+/gi;
    if (regex.test(totalByDayArray[i]))
    {
      store.perDay.push(totalByDayArray[i]);
    }
  }
}

function employeeCreate_(empCode)
{
  this.empCode = empCode;
  this.totalSale = 0;
  this.weekSale = [0, 0, 0, 0, 0, 0, 0]
  this.target = [0, 0, 0, 0, 0, 0, 0]
  this.difference = [0, 0, 0, 0, 0, 0, 0]
  this.totalTarget = 0;
  this.totalDifference = 0;
}

function storeCreate_(storeName)
{
  this.store = storeName;
  this.totalWeek = 0;
  this.perDay = [];
}

function actualSalesPerEmployee_(actualArray, employee)
{
  var days = [];
  days = countDays_(actualArray);
  days.splice(0, 0, 0);
  var fringe = new employeeCreate_("UNSCHEDULED");
  
  for (var h = 0; h < days.length; h++)
  {
    for (var i = days[h]; i < days[h + 1]; i++)
    {
      var check = 0;
      for (var j = 0; j < employee.length; j++)
      {
        if (actualArray[3][i] == employee[j].empCode)
        {
          employee[j].totalSale += actualArray[1][i];
          employee[j].weekSale[h] = actualArray[1][i];
        }
        else
        {
          check++;
        }
        
        if (check == employee.length)
        {
          fringe.totalSale += actualArray[1][i];
          fringe.weekSale[h] += actualArray[1][i];
        }
      }
    }
  }
  if (fringe.totalSale != 0)
  {
    employee.push(fringe);
  }
  return employee;
}

function employeeWrite_(employeeObjects, indPerHour, individualTarget, timeIntervalAmount, employee)
{
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName("EmployeeHours");
  var format = ss.getRangeByName("formattingDate");
  var temp1 = sheet.getRange("C1");
  var name = temp1.getValue();
  var temp2 = sheet.getRange("C2");
  var dateStart = Utilities.formatDate(temp2.getValue(),"GMT","MM/dd/yyyy");
  var temp3 = sheet.getRange("C3");
  var dateEnd = Utilities.formatDate(temp3.getValue(),"GMT","MM/dd/yyyy");
  var store = new storeCreate_(name);
  var sheet = ss.getSheetByName("Results");
  var totalByDay = ss.getRangeByName("totalPerDay");
  var totalByDayArray = arrayTranspose_(totalByDay.getValues());
  
  filterStorePerDay_(totalByDayArray, store);
  
  var sheet = ss.getSheetByName("Actuals");
  var lastPollCell = sheet.getRange("E1");
  var lastPoll = "";
  
  if (lastPollCell.getValue() == "" || lastPollCell.getValue() == null)
  {
    lastPoll = "Not Applicable";
  }
  else
  {
    lastPoll = Utilities.formatDate(lastPollCell.getValue(),"CST","EEE, d MMM yyyy h:mm a ");
  } 
  
  var sheet = ss.getSheetByName("Employee");
  var employeeReset = ss.getRangeByName("EmployeeReset");
  var cell = sheet.getRange('B1');
  
  employeeReset.clear()
  format.merge();
  cell.offset(0, 0).setValue("Target sales for store code: " + name + " between dates " + dateStart + " and " + dateEnd + " using polled data obtained on " + lastPoll + " version 4.03.");
  
  var cell = sheet.getRange('A1');
  
  var row = 3;
  
  for (var x = 0; x < indPerHour.length; x++)
  {
    var weekTotal = 0;
    var column = 1;
    cell.offset(row, column-1).setValue(employeeObjects[x].employee);
    var check = 0;
    for (var y = 0; y < indPerHour[x].length; y++)
    {
      var value = 0;
      for (var z = 0; z < timeIntervalAmount; z++)
      {
        if (indPerHour[x][y][z] == 1)
        {
          if (individualTarget[y][z] != 0)
          {
            value += individualTarget[y][z];
          }
        }
      }
      weekTotal += value;
      cell.offset(row, column).setValue(value);
      employee[row - 3].target[check] = value;
      check++;
      column += 3;
    }
    cell.offset(row, column).setValue(weekTotal);
    employee[row - 3].totalTarget = weekTotal;
    row++;
  }
  
  row = 3; 
  
  if (employee[employee.length - 1].empCode == "UNSCHEDULED")
  {
    cell.offset(employee.length - 1 + row, 0).setValue(employee[employee.length - 1].empCode);
  }
  
  for (var i = 0; i < employee.length; i++)
  {
    column = 2;
    for (var j = 0; j < employee[i].weekSale.length; j++)
    {
      cell.offset(row, column).setValue(employee[i].weekSale[j]);
      column++;
      employee[i].difference[j] = employee[i].weekSale[j] - employee[i].target[j];
      if (employee[i].difference[j] >= 0)
      {
        cell.offset(row, column).setFontColor("green")
      }
      else
      {
        cell.offset(row, column).setFontColor("purple")
      }
      cell.offset(row, column).setValue(employee[i].difference[j]);
      column += 2;
    }
    cell.offset(row, column).setValue(employee[i].totalSale);
    column++;
    employee[i].totalDifference = employee[i].totalSale - employee[i].totalTarget;
    if (employee[i].totalDifference >= 0)
    {
      cell.offset(row, column).setFontColor("green")
    }
    else
    {
      cell.offset(row, column).setFontColor("purple")
    }
    cell.offset(row, column).setValue(employee[i].totalDifference);
    row++;
  }
  
  row++;
  column = 0;
  cell.offset(row, column).setValue("Assigned Sales Target");
  
  var columnName = ["B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y"];
  
  for (var b = 0; b < columnName.length; b++)
  {
    var cell = sheet.getRange(columnName[b] + (row + 1));
    cell.setFormulaR1C1("=SUM(R[-" + (row) + "]C[0]:R[-1]C[0])");
  }
  
  column = (-1 * columnName.length);
  row = 1;
  cell.offset(row, column).setValue("Total Sales Target");
  column++;
  
  for (var c = 0; c < store.perDay.length; c++)
  {
    cell.offset(row, column).setValue(store.perDay[c]);
    column += 3;
  }
}

function createArray_(length) 
{
  var arr = new Array(length || 0),
      i = length;
  if (arguments.length > 1) 
  {
    var args = Array.prototype.slice.call(arguments, 1);
    while(i--) 
    {
      arr[length-1 - i] = createArray_.apply(this, args);
    }
  }
  return arr;
}

function indCount_(employeeObjects, array, methodName, employeeID, storeTarget)
{
  var indByDayArray = [];
  var individualArray = [];
  var index2 = 0;
  
  for (var i = employeeID; i < (employeeID + 1); i++)
  {
    for (var index = 0; index < 25; index = (index + 4))
    {
      zeroArray_(indByDayArray);
      
      if (employeeObjects[i][methodName[index]] == null || employeeObjects[i][methodName[index+2]] == null)
      {
        employeeObjects[i][methodName[index]] = "00:00";
        employeeObjects[i][methodName[index + 2]] = "00:00";
      }
      if (employeeObjects[i][methodName[index+1]] == null || employeeObjects[i][methodName[index+3]] == null)
      {
        employeeObjects[i][methodName[index + 1]] = "00:00";
        employeeObjects[i][methodName[index + 3]] = "00:00"; 
      }
      
      var slot1 = 2 * decimalHours_(employeeObjects[i][methodName[index]], 1);
      var slot2 = 2 * decimalHours_(employeeObjects[i][methodName[index+1]], 1);
      
      var timeDifference1 = ((2 * decimalHours_(employeeObjects[i][methodName[index + 2]], 1)) - (2 * decimalHours_(employeeObjects[i][methodName[index]], 1)));
      var timeDifference2 = ((2 * decimalHours_(employeeObjects[i][methodName[index + 3]], 1)) - (2 * decimalHours_(employeeObjects[i][methodName[index + 1]], 1)));
      
      indByDayArray = addEmp_(array, slot1, timeDifference1);
      indByDayArray = addEmp_(array, slot2, timeDifference2);
      
      individualArray[index2] = indByDayArray.splice(0);
      index2++;
    }
  }
  return individualArray;
}


function loadVersionFromSQL_()
{
  var conn = Jdbc.getConnection(sqlServerConnection, sqlServerReadUser, sqlServerReadPass);
  var stmt = conn.createStatement();
  var ret  = stmt.execute("USE EBTGOOGLE;");
  var rs   = stmt.executeQuery("Select version, DisablePreviousVersion from dbo.GoogleDocVersion where [Application] = 'SalesTracker'");
  rs.next();

  var info = [];
  info[0] = rs.getString(1);
  info[1] = rs.getString(2);
  
  rs.close();
  stmt.close();
  conn.close();
  
  return info;
}



function getTargetPosition_(dataArray, days, index)
{ 
  var data = [];
  zeroArray_(data);
  var dayElement = 0;
  
  if (index == 0)
  {
    dayElement = 0;
  }
  else
  {
    dayElement = days[index - 1];
  }
  
  for (i = dayElement; i < days[index]; i++)
  {
    var position = dataArray[4][i];
    data[2 * position] = (dataArray[6][i] / 2);
    data[2 * position + 1] = (dataArray[6][i] / 2);
  }
  
  return data;
}

function countDays_(sqlArray)
{

  var amountDays = [];
  var sun = 0;
  var mon = 0;
  var tue = 0;
  var wed = 0;
  var thu = 0;
  var fri = 0;
  var sat = 0;


  // sqlArray[0].length: is the number of rows in the Targets
  // sheet

  for (var i = 0; i < sqlArray[0].length; ++i)
  {
    switch (sqlArray[2][i])
    {
      case 1:
        sun++;
      case 2:
        mon++;
      case 3:
        tue++;
      case 4:
        wed++;
      case 5:
        thu++;
      case 6:
        fri++;
      case 7:
        sat++;
    } 
  }
  amountDays.push(sun, mon, tue, wed, thu, fri, sat);
  return amountDays;
}

function assignTarget_(dataArray, height, headerHeight)
{
  var storeTarget = [];
  var index = 0;
  var columnValue = ["B", "F", "J", "N", "R", "V", "Z"];

  // I don't fully understand this 'days' stuff
  var days = countDays_(dataArray);
  var sheetName = "Results";
  
  for (var i = 0; i < columnValue.length; i++)
  {
    storeTarget[i] = getTargetPosition_(dataArray, days, index);  
    index++;
    columnWrite_(sheetName, columnValue[i], storeTarget[i], height, headerHeight);
  }
  
  return storeTarget;
}  

function getEmployeeAmount_(employeeObjects, height, headerHeight, storeTarget, employee)
{
  var blankArray = [];
  var values2 = [];
  var groupPerHour = [];
  var indPerHour = [];
  var index = 0;
  var methodName = comboArray_(employeeObjects);
  var columnValue = ["C", "G", "K", "O", "S", "W", "AA"];
  var employeeID = 0;
  var sheetName = "Results";
  
  for (var i = 0; i < columnValue.length; i++)
  {
    zeroArray_(blankArray);
    values = empCount_(employeeObjects, blankArray, methodName, index);
    groupPerHour[i] = values.slice(0)
    index += 4;
    columnWrite_(sheetName, columnValue[i], groupPerHour[i], height, headerHeight);
  }
  
  for (var j = 0; j < employeeObjects.length; j++)
  {
    zeroArray_(values2);
    indPerHour[j] = indCount_(employeeObjects, values2, methodName, employeeID, storeTarget);
    employeeID++;
  }
  
  individualTarget_(employeeObjects, storeTarget, groupPerHour, indPerHour, height, headerHeight, employee);
}

function comboArray_(employeeObjects)
{
  var string = [];
  var week = ["sun", "mon", "tue", "wed", "thu", "fri", "sat"];
  var flag = ["in", "out"];
  var split = ["a", "b"];
  for (var i = 0; i < employeeObjects.length; i++)
  {
    for (var j = 0; j < week.length; j++)
    {
      for (var k = 0; k < flag.length; k++)
      {
        for (var l = 0; l < split.length; l++)
        { 
          string.push(week[j] + flag[k] + split[l]);
        }
      }
    }
  }
  return string;
}

function empCount_(employeeObjects, blankArray, methodName, index)
{
  var totalEmpCountArray = [];
  for (var i = 0; i < employeeObjects.length; i++)
  {
    
    if (employeeObjects[i][methodName[index]] == null || employeeObjects[i][methodName[index+2]] == null)
    {
      employeeObjects[i][methodName[index]] = "00:00";
      employeeObjects[i][methodName[index+2]] = "00:00";
    }
    if (employeeObjects[i][methodName[index+1]] == null || employeeObjects[i][methodName[index+3]] == null)
    {
      employeeObjects[i][methodName[index+1]] = "00:00";
      employeeObjects[i][methodName[index+3]] = "00:00"; 
    }
    if (employeeObjects[i][methodName[index]] == null || employeeObjects[i][methodName[index+2]] == null)
    {
      employeeObjects[i][methodName[index]] = "00:00";
      employeeObjects[i][methodName[index + 2]] = "00:00";
    }
    if (employeeObjects[i][methodName[index+1]] == null || employeeObjects[i][methodName[index+3]] == null)
    {
      employeeObjects[i][methodName[index + 1]] = "00:00";
      employeeObjects[i][methodName[index + 3]] = "00:00"; 
    }  
    var slot1 = 2 * decimalHours_(employeeObjects[i][methodName[index]], 1);
    var slot2 = 2 * decimalHours_(employeeObjects[i][methodName[index+1]], 1);
    
    var timeDifference1 = ((2 * decimalHours_(employeeObjects[i][methodName[index+2]], 1)) - (2 * decimalHours_(employeeObjects[i][methodName[index]], 1)));
    var timeDifference2 = ((2 * decimalHours_(employeeObjects[i][methodName[index+3]], 1)) - (2 * decimalHours_(employeeObjects[i][methodName[index+1]], 1)));
    
    totalEmpCountArray = addEmp_(blankArray, slot1, timeDifference1);
    totalEmpCountArray = addEmp_(blankArray, slot2, timeDifference2);
  }
  return totalEmpCountArray;
}

function addEmp_(blankArray, slot, time)
{
  for (var l = slot; l < ((slot + time)); l++)
  {
    blankArray[l]++;
  }
  return blankArray;
}

function zeroArray_(array)
{
  for (var i = 0; i < 48; i++)
  {
    array[i] = 0;
  }
}

function columnWrite_(sheetName, columnLetter, array, height, headerHeight)
{
  var ss = SpreadsheetApp.getActiveSpreadsheet();
  var sheet = ss.getSheetByName(sheetName);
  for (var i = headerHeight; i < height + headerHeight; i++)
  {
    var value = columnLetter + i
    var range = sheet.getRange(value);
    i = i - headerHeight;
    range.setValue(array[i]);
    i = i + headerHeight;
  }
}

function decimalHours_(string, roundBool) 
{
  var m = /(\d+:\d+)/.exec(string);
  var n = m[0];
  var o = Number(n.substring(0, 2))
  var p = Number(n.substring(3, 5))
  
  if (roundBool == 1)
  {
    if (((p/60) > .25) && ((p/60) < .75))
    {
      p = .5
    }
    else if ((p/60) <= .25)
    {
      p = Math.floor(p/60);
    }
    else
    {
      p = Math.ceil(p/60);
    }
  }
  else
  {
    p = p / 60;
    p.toPrecision(2);
  }
  
  return (o + p);
}



function militaryHours_(decimalHours)
{
  var hour = Math.floor(decimalHours);
  if (hour < 10)
  {
    hour = "0" + hour;
  }

  var minute = Math.round(((decimalHours - hour) * 60),2);
  if (minute < 10)
  {
    minute = "0" + minute;
  }
  
  return hour + ":" + minute;
}

// getRowsData iterates row by row in the input range and returns an array of objects.
// Each object contains all the data for a given row, indexed by its normalized column name.
// Arguments:
//   - sheet: the sheet object that contains the data to be processed
//   - range: the exact range of cells where the data is stored
//   - columnHeadersRowIndex: specifies the row number where the column names are stored.
//       This argument is optional and it defaults to the row immediately above range; 
// Returns an Array of objects.
function getRowsData_(sheet, range, columnHeadersRowIndex) {
  columnHeadersRowIndex = columnHeadersRowIndex || range.getRowIndex() - 1;
  var numColumns = range.getLastColumn() - range.getColumn() + 1;
  var headersRange = sheet.getRange(columnHeadersRowIndex, range.getColumn(), 1, numColumns);
  var headers = headersRange.getValues()[0];
  return getObjects_(range.getValues(), normalizeHeaders_(headers));
}

// For every row of data in data, generates an object that contains the data. Names of
// object fields are defined in keys.
// Arguments:
//   - data: JavaScript 2d array
//   - keys: Array of Strings that define the property names for the objects to create
function getObjects_(data, keys) {
  var objects = [];
  for (var i = 0; i < data.length; ++i) {
    var object = {};
    var hasData = false;
    for (var j = 0; j < data[i].length; ++j) {
      var cellData = data[i][j];
      if (isCellEmpty_(cellData)) {
        continue;
      }
      object[keys[j]] = cellData;
      hasData = true;
    }
    if (hasData) {
      objects.push(object);
    }
  }
  return objects;
}

// Returns an Array of normalized Strings.
// Arguments:
//   - headers: Array of Strings to normalize
function normalizeHeaders_(headers) {
  var keys = [];
  for (var i = 0; i < headers.length; ++i) {
    var key = normalizeHeader_(headers[i]);
    if (key.length > 0) {
      keys.push(key);
    }
  }
  return keys;
}

// Normalizes a string, by removing all alphanumeric characters and using mixed case
// to separate words. The output will always start with a lower case letter.
// This function is designed to produce JavaScript object property names.
// Arguments:
//   - header: string to normalize
// Examples:
//   "First Name" -> "firstName"
//   "Market Cap (millions) -> "marketCapMillions
//   "1 number at the beginning is ignored" -> "numberAtTheBeginningIsIgnored"
function normalizeHeader_(header) {
  var key = "";
  var upperCase = false;
  for (var i = 0; i < header.length; ++i) {
    var letter = header[i];
    if (letter == " " && key.length > 0) {
      upperCase = true;
      continue;
    }
    if (!isAlnum_(letter)) {
      continue;
    }
    if (key.length == 0 && isDigit_(letter)) {
      continue; // first character must be a letter
    }
    if (upperCase) {
      upperCase = false;
      key += letter.toUpperCase();
    } else {
      key += letter.toLowerCase();
    }
  }
  return key;
}

// Returns true if the cell where cellData was read from is empty.
// Arguments:
//   - cellData: string
function isCellEmpty_(cellData) {
  return typeof(cellData) == "string" && cellData == "";
}

// Returns true if the character char is alphabetical, false otherwise.
function isAlnum_(char) {
  return char >= 'A' && char <= 'Z' ||
    char >= 'a' && char <= 'z' ||
      isDigit_(char);
}

// Returns true if the character char is a digit, false otherwise.
function isDigit_(char) {
  return char >= '0' && char <= '9';
}

// Given a JavaScript 2d Array, this function returns the transposed table.
// Arguments:
//   - data: JavaScript 2d Array
// Returns a JavaScript 2d Array
// Example: arrayTranspose([[1,2,3],[4,5,6]]) returns [[1,4],[2,5],[3,6]].
function arrayTranspose_(data) {
  if (data.length == 0 || data[0].length == 0) {
    return null;
  }
  
  var ret = [];
  for (var i = 0; i < data[0].length; ++i) {
    ret.push([]);
  }
  
  for (var i = 0; i < data.length; ++i) {
    for (var j = 0; j < data[i].length; ++j) {
      ret[j][i] = data[i][j];
    }
  }
  return ret;
}


// getColumnsData iterates column by column in the input range and returns an array of objects.
// Each object contains all the data for a given column, indexed by its normalized row name.
// Arguments:
//   - sheet: the sheet object that contains the data to be processed
//   - range: the exact range of cells where the data is stored
//   - rowHeadersColumnIndex: specifies the column number where the row names are stored.
//       This argument is optional and it defaults to the column immediately left of the range; 
// Returns an Array of objects.
function getColumnsData_(sheet, range, rowHeadersColumnIndex) {
  rowHeadersColumnIndex = rowHeadersColumnIndex || range.getColumnIndex() - 1;
  var headersTmp = sheet.getRange(range.getRow(), rowHeadersColumnIndex, range.getNumRows(), 1).getValues();
  var headers = normalizeHeaders_(arrayTranspose_(headersTmp)[0]);
  return getObjects_(arrayTranspose_(range.getValues()), headers);
}


///   Writing Data
// This is where the data used in this example will be retrieved from:
// https://docs.google.com/spreadsheet/ccc?key=0AlNd4P4KLiq8cktUT0xINDFIT0syZ0xvc2Y3ZDZQMWc#gid=0
var DATA_SPREADSHEET_ID = "0AlNd4P4KLiq8cktUT0xINDFIT0syZ0xvc2Y3ZDZQMWc"


// setRowsData fills in one row of data per object defined in the objects Array.
// For every Column, it checks if data objects define a value for it.
// Arguments:
//   - sheet: the Sheet Object where the data will be written
//   - objects: an Array of Objects, each of which contains data for a row
//   - optHeadersRange: a Range of cells where the column headers are defined. This
//     defaults to the entire first row in sheet.
//   - optFirstDataRowIndex: index of the first row where data should be written. This
//     defaults to the row immediately below the headers.
function setRowsData_(sheet, objects, optHeadersRange, optFirstDataRowIndex) {
  var headersRange = optHeadersRange || sheet.getRange(1, 1, 1, sheet.getMaxColumns());
  var firstDataRowIndex = optFirstDataRowIndex || headersRange.getRowIndex() + 1;
  var headers = normalizeHeaders_(headersRange.getValues()[0]);
  
  var data = [];
  for (var i = 0; i < objects.length; ++i) {
    var values = []
    for (j = 0; j < headers.length; ++j) {
      var header = headers[j];
      // If the header is non-empty and the object value is 0...
      if ((header.length > 0) && (objects[i][header] == 0)) {
        values.push(0);
      }
      // If the header is empty or the object value is empty...
      else if ((!(header.length > 0)) || (objects[i][header]=='')) {
        values.push('');
      }
      else {
        values.push(objects[i][header]);
      }
    }
    data.push(values);
  }
  
  var destinationRange = sheet.getRange(firstDataRowIndex, headersRange.getColumnIndex(),
                                        objects.length, headers.length);
  destinationRange.setValues(data);
}

