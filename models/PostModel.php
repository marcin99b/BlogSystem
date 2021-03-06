<?php

class PostModel extends Model
{
  function __construct()
  {
    parent::__construct();

  }

  public function create($title = null, $content = null, $footer = null)
  {
    if (null === $title || null === $content)
    {
		return false;
	}
    //Prepare data of date
    $date = getdate(date("U"));
      $day = $date['mday'];
      $month = $date['mon'];
      $year = $date['year'];
      if ($day < 10) $day = '0' .  (string)$day;
      if ($month < 10) $month = '0' . (string)$month;
      $dateToAdd = $day . "." . $month . "." . $year;

    //Edit textarea value
    $text = trim($content);
      $textAr = explode("\n", $text);
      $textAr = array_filter($textAr, 'trim');
      $content = '';
      foreach ($textAr as $line)
      {
      $content .= $line . '<br>';
      }

    // Add to database
    $postToAdd = $this -> pdo-> prepare('INSERT INTO `posts`(`title`, `date`, `content`, `footer`, `authorId`) VALUES (:title, :datetime, :content, :footer, :autorId)');
      $postToAdd->bindParam(':title', $title);
      $postToAdd->bindParam(':datetime', $dateToAdd);
      $postToAdd->bindParam(':content', $content);
      $postToAdd->bindParam(':footer', $footer);
      $postToAdd->bindParam(':autorId', $_SESSION['userId']);
      $postToAdd->execute();


  }
  //Select post (write query in controller)
  public function select($fromPage, $numberPage)
  {
    $postToSelect = $this -> pdo ->query('SELECT
			posts.id,
			posts.title,
			posts.date,
			posts.content,
			posts.footer,
			posts.keySentence,
			posts.authorId,
			users.id AS userId,
			users.login AS authorName
		from posts
		INNER JOIN users ON posts.authorId = users.id
		ORDER BY posts.id DESC
		LIMIT '. $fromPage .', '. $numberPage .'');

    return $postToSelect;
  }

  public function update($id = null, $title = null, $content = null, $footer = null)
  {
	if (null === $id)
	{
	return false;
	}
	//Edit textarea value
	$text = trim($_POST['content']);
	$textAr = explode("\n", $text);
	$textAr = array_filter($textAr, 'trim');
	$content = '';
	foreach ($textAr as $line)
	{
	$content .= $line . '<br>';
	}
	// Add to database
	$postToUpdate = $this -> pdo -> prepare( 'UPDATE `posts`
			SET
			`title` = :title,
			`content` = :content,
			`footer` = :footer
			WHERE id = :postid');
		$postToUpdate->bindParam(':postid', $id);
		$postToUpdate->bindParam(':title', $title);
		$postToUpdate->bindParam(':content', $content);
		$postToUpdate->bindParam(':footer', $footer);
		$postToUpdate->execute();

  }

  public function delete($id)
  {
    //Delete from database
    $postToDelete = $this -> pdo -> prepare('DELETE FROM `posts` WHERE id = :postid ');
        $postToDelete->bindParam(':postid', $id);
        $postToDelete->execute();
  }

  public function pagination($idCurrentPage = 1)
  {
	//Default numbers posts in one page
	$numberPostsToShow = 10;

    $fromNumberPostInDatabase = ($idCurrentPage -1) * $numberPostsToShow;

    //Calc number of pages
    $countPostInDatabase = $this -> pdo ->query('SELECT COUNT(id) AS countPost FROM `posts`')->fetch()['countPost'];

    //If show last page, and can't show default number of posts, show all others
    if(($fromNumberPostInDatabase + $numberPostsToShow) > $countPostInDatabase)
      $numberPostsToShow = $countPostInDatabase - $fromNumberPostInDatabase;

    $highestNumberInPagination = round($countPostInDatabase/$numberPostsToShow);
    if($highestNumberInPagination * $numberPostsToShow < $countPostInDatabase)
      $highestNumberInPagination++;

    //Prepare info about pages, to return
    $infoToReturn =
    [
      'from' => $fromNumberPostInDatabase,
      'number' => $numberPostsToShow,
      'max' => $highestNumberInPagination
    ];
    return $infoToReturn;
  }
}
