class TimelineController {
  private $dbConnection;
  
  public function __construct($dbConnection) {
    $this->dbConnection = $dbConnection;
  }

  public function fetchPublicTimeline() {
    // Fetch the public timeline data using $this->dbConnection
    $timeline = new Timeline($this->dbConnection);
    $data = $timeline->fetchPublicTimeline();
    
    // Return the data as JSON
    return json_response($data);
  }
}
