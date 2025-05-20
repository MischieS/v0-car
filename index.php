<?php
// Basic PHP script demonstrating core features

// Variables and data types
$name = "User";
$age = 30;
$isLoggedIn = true;
$items = ["Item 1", "Item 2", "Item 3"];

// Function definition
function greet($name, $timeOfDay = "day") {
    return "Good $timeOfDay, $name!";
}

// Conditional statements
if ($isLoggedIn) {
    $status = "logged in";
} else {
    $status = "logged out";
}

// Class definition
class Person {
    private $name;
    private $age;
    
    public function __construct($name, $age) {
        $this->name = $name;
        $this->age = $age;
    }
    
    public function getDetails() {
        return "Name: {$this->name}, Age: {$this->age}";
    }
}

// Create an object
$person = new Person($name, $age);

// Output with HTML
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Example</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; line-height: 1.6; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { border: 1px solid #ddd; padding: 20px; margin-bottom: 20px; border-radius: 5px; }
        .items { list-style-type: none; padding: 0; }
        .items li { padding: 8px; background-color: #f4f4f4; margin-bottom: 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo greet($name, "morning"); ?></h1>
        
        <div class="card">
            <h2>User Information</h2>
            <p>Status: You are <?php echo $status; ?></p>
            <p><?php echo $person->getDetails(); ?></p>
        </div>
        
        <div class="card">
            <h2>Items List</h2>
            <ul class="items">
                <?php foreach($items as $item): ?>
                    <li><?php echo $item; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <?php
        // Loop example
        echo "<div class='card'>";
        echo "<h2>Loop Example</h2>";
        for ($i = 1; $i <= 5; $i++) {
            echo "<p>This is iteration number $i</p>";
        }
        echo "</div>";
        ?>
    </div>
</body>
</html>
