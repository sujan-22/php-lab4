<?php

// I, Sujan Rokad, 000882948, certify that this material is my original work.
// No other person's work has been used without suitable acknowledgement and I
// have not made my work available to anyone else.

/**
 * Infinite Scroll for Quotes
 *
 * @author Sujan Rokad
 * @version 202335.00
 * @package COMP 10260 Assignment 4
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Establish a PDO connection to the database
    $pdo = new PDO("mysql:host=localhost;dbname=sa000882948", "sa000882948", "Sa_20031007");
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die(json_encode(['error' => 'Database connection failed.']));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['page'])) {
    // Handle AJAX request for quotes

    /**
     * Function to generate a Bootstrap 5 card HTML string with random colors
     *
     * @param string $quoteText - The text of the quote
     * @param string $authorName - The name of the author
     * @return string - The HTML string for the Bootstrap 5 card
     */
    function generateCard($quoteText, $authorName)
    {
        // Array of possible Bootstrap background color classes for the author and quote text
        $authorBgColors = ['bg-primary', 'bg-secondary', 'bg-danger', 'bg-warning', 'bg-info', 'bg-dark'];
        $quoteBgColors = ['bg-primary', 'bg-secondary', 'bg-danger', 'bg-warning', 'bg-info', 'bg-dark'];

        // Randomly select background colors for the author and quote text
        $randomAuthorColor = $authorBgColors[array_rand($authorBgColors)];
        $randomQuoteColor = $quoteBgColors[array_rand($quoteBgColors)];

        // Determine text color based on background color
        $authorTextColor = in_array($randomAuthorColor, ['bg-info', 'bg-warning']) ? 'text-black' : 'text-white';
        $quoteTextColor = in_array($randomQuoteColor, ['bg-info', 'bg-warning']) ? 'text-black' : 'text-white';

        // HTML for the card with random background colors and text colors
        return '<div class="card mb-3 a4card w-100">
                    <div class="card-header ' . $randomAuthorColor . ' ' . $authorTextColor . '">' . $authorName . '</div>
                    <div class="card-body d-flex align-items-center ' . $randomQuoteColor . '">
                        <p class="card-text w-100 ' . $quoteTextColor . '">' . $quoteText . '</p>
                    </div>
                </div>';
    }

    // Validate and sanitize the 'page' parameter
    $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, array(
        'options' => array('min_range' => 1)
    ));

    if ($page === false) {
        // Invalid page parameter
        die(json_encode(['error' => 'Invalid page parameter.']));
    }

    // Set the limit of quotes per page
    $limit = 20;

    // Calculate the offset based on the current page
    $offset = ($page - 1) * $limit;

    // Prepare and execute the database query
    try {
        $query = "SELECT quotes.quote_text, authors.author_name
                  FROM quotes
                  JOIN authors ON quotes.author_id = authors.author_id
                  LIMIT :per_page
                  OFFSET :offset";

        $statement = $pdo->prepare($query);
        $statement->bindParam(':per_page', $limit, PDO::PARAM_INT);
        $statement->bindParam(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        // Fetch quotes from the database
        $quotes = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Generate HTML cards for the fetched quotes
        $htmlCards = array_map(function ($quote) {
            return generateCard($quote['quote_text'], $quote['author_name']);
        }, $quotes);

        // Return the JSON-encoded array of HTML cards
        echo json_encode($htmlCards);
    } catch (PDOException $e) {
        // Handle database query error
        die(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
    }
} else {
    // If accessed directly, output an error
    die(json_encode(['error' => 'Invalid access.']));
}
?>
