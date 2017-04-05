<?php
  date_default_timezone_set('America/Los_Angeles');
  require_once __DIR__."/../vendor/autoload.php";
  require_once __DIR__."/../src/Book.php";
  require_once __DIR__."/../src/Author.php";

  use Symfony\Component\Debug\Debug;
  Debug::enable();

  $app = new Silex\Application();

  $app['debug'] = true;

  $server = 'mysql:host=localhost:8889;dbname=library';
  $user = 'root';
  $pass = 'root';
  $db = new PDO($server, $user, $pass);

  $app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views'
  ));

  use Symfony\Component\HttpFoundation\Request;
  Request::enableHttpMethodParameterOverride();

  $app->get("/", function () use ($app) {
    $msg = '';
    return $app['twig']->render('index.html.twig', array('msg'=>$msg));
  });

  $app->get("/addbook", function () use ($app) {
    return $app['twig']->render('addbook.html.twig', array('books'=>Book::getAll()));
  });

  $app->post("/addbook", function () use ($app) {
    if(empty($_POST['book'])){
      return $app['twig']->render('warning.html.twig');
    } else {
      $new_book = new Book($_POST['book']);
      $new_book->save();
      return $app['twig']->render('addbook.html.twig', array('books'=>Book::getAll()));
    }
  });

  $app->get("/book/{id}", function ($id) use ($app) {
    return $app['twig']->render('book.html.twig', array('book'=>Book::find($id), 'authors'=>Author::getAuthors($id)));
  });

  $app->post("/addauthor", function () use ($app) {
    $authors = Author::getAllNames();
    if(in_array($_POST['author'], $authors)){
      $new_author = Author::getByName($_POST['author']);
      $new_author->saveBook($_POST['book_id']);
      return $app['twig']->render('book.html.twig', array('book'=>Book::find($_POST['book_id']), 'authors'=>Author::getAuthors($_POST['book_id'])));
    } else {
      $new_author = new Author($_POST['author']);
      $new_author->save();
      $new_author->saveBook($_POST['book_id']);
      return $app['twig']->render('book.html.twig', array('book'=>Book::find($_POST['book_id']), 'authors'=>Author::getAuthors($_POST['book_id'])));
    }
  });

  $app->get("/author/{id}", function ($id) use ($app) {
    $new_author = Author::find($id);
    $books = $new_author->findbooks();
    return $app['twig']->render('author.html.twig', array('author'=>Author::find($id), 'books'=>$books));
  });

  $app->post("/search", function () use ($app) {
    $book_array = Book::getAllBooks();
    $author_array = Author::getAllNames();
    if(in_array($_POST['srch-term'], $book_array)){
      $author = '';
      $new_book = Book::findByBook($_POST['srch-term']);
      return $app['twig']->render('result.html.twig', array('book'=>$new_book, 'author'=>$author));
    } else if(in_array($_POST['srch-term'], $author_array)){
      $book = '';
      $new_author = Author::getByName($_POST['srch-term']);
      return $app['twig']->render('result.html.twig', array('author'=>$new_author, 'book'=>$book));
    } else {
      $msg = "No Match!";
      return $app['twig']->render('index.html.twig', array('msg'=>$msg));
    }

  });

  return $app;
?>
