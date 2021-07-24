<?php
require 'includes/functions.php';

$message = '';
$prod_id = '';
$search_id = '';
$products = '';
session_start();

if (isset($_COOKIE['error_message'])) {
    $message = '<div class="alert alert-danger text-center">'
        . $_COOKIE['error_message'] .
        '</div>';
    setcookie('error_message', null, time() - 3600);
}
if (count($_GET) > 0) {
    if (!empty($_GET['term']) && preg_match("/^([a-zA-Z' ]+)$/", $_GET['term'])) {
        $term  = $_GET['term'];
        $search_id = searchProduct($term);
    } else if ($_GET['from'] == 'signup') {
        $message = '<div class="alert alert-success text-center">Thank you for signing in!
        </div>';
    } elseif ($_GET['from'] == 'login') {
        $message = '<div class="alert alert-success text-center">Thank you for logging in!
        </div>';
    } elseif ($_GET['from'] == 'newItem') {
        $message = '<div class="alert alert-success text-center">New item uploaded!
        </div>';
    } elseif ($_GET['from'] == 'products' && preg_match("/^[0-9]+$/", $_GET['id'])) {
        $prod_id = $_GET['id'];
        if (isset($_SESSION['lastviewed'])) {
            var_dump($_SESSION['lastviewed']);
            unset($_SESSION['lastviewed']);
        }
    }
}

if (!empty($search_id)) {
    $products =
        getProduct($search_id);
} else {
    $products = getAllProducts();
}
$recently_viewed_products = getProduct($prod_id);



?>

<!DOCTYPE html>
<html>

<head>
    <title>COMP 3015</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="css/style.css" rel="stylesheet">
</head>

<body>

    <div id="wrapper">

        <div class="container">

            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <h1 class="login-panel text-center text-muted">
                        COMP 3015 Final Project
                    </h1>
                    <hr />
                </div>
            </div>
            <?php echo $message; ?>
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <?php if (isset($_SESSION['loggedin'])) {
                        echo '<button class="btn btn-default" data-toggle="modal" data-target="#newItem"><i class="fa fa-photo"></i> New Item</button>';
                        echo '<a href="logout.php" class="btn btn-default pull-right"><i class="fa fa-sign-out"> </i> Logout</a>';
                    } else if (!isset($_SESSION['signedin'])) {
                        echo ' <a href="#" class="btn btn-default pull-right" data-toggle="modal" data-target="#login"><i class="fa fa-sign-in"> </i> Login</a>';
                        echo '<a href="#" class="btn btn-default pull-right" data-toggle="modal" data-target="#signup"><i class="fa fa-user"> </i> Sign Up</a>';
                    } ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <h2 class="login-panel text-muted">
                        Items For Sale
                    </h2>
                    <hr />
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <form class="form-inline" method="get" action="index.php?from=search&term=">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon"><i class="fa fa-search"></i></div>
                                <input type="text" class="form-control" placeholder="Search" name="term" />
                            </div>
                        </div>
                        <input type="submit" class="btn btn-default" value="Search" />
                        <button class="btn btn-default" data-toggle="tooltip" title="Shareable Link!"><i class="fa fa-share"></i></button>
                    </form>
                    <br />
                </div>
            </div>
            <div class="row">

                <?php foreach ($products as $product) { ?>
                    <div class="col-md-3">
                        <?php if (isset($_SESSION['signedin']) || isset($_SESSION['loggedin'])) {
                            echo '<div class="panel panel-warning">';
                        } else {
                            echo  '<div class="panel panel-info">';
                        } ?>
                        <div class="panel-heading">
                            <?php if (isset($_SESSION['loggedin'])) {
                                echo '<a class="" href="" data-toggle="tooltip" title="Unpin item">
                                    <i class="fa fa-dot-circle-o"></i></a>';
                            } ?>
                            <?php echo '<span>' . $product['title'] . '</span>'; ?>

                            <span class="pull-right">
                                <?php if (isset($_SESSION['email']) && $product['email'] == $_SESSION['email']) {
                                    echo '<a class="" href="delete.php?id=' . $product['id'] . '" data-toggle="tooltip" title="Delete item">
                                        <i class="fa fa-trash"></i></a>';
                                } ?>
                            </span>
                        </div>
                        <div class="panel-body text-center">
                            <p>
                                <?php echo
                                '<a href="product.php?id=' . $product['id'] . '">
                                        <img class="img-rounded img-thumbnail" src="products/' . $product['picture'] . '" />
                                    </a>';
                                ?>
                            </p>
                            <?php echo
                            '<p class="text-muted text-justify">' . $product['description'] . '</p>'; ?>
                            <?php if (isset($_SESSION['loggedin'])) {
                                echo '<a class="pull-left" href="" data-toggle="tooltip" title="Downvote item">
                                    <i class="fa fa-thumbs-down"></i></a>';
                            } ?>
                        </div>
                        <?php
                        $email = $product['email'];
                        $uname = getUsername($email);
                        ?>
                        <div class="panel-footer ">
                            <span><a href="mailto:fakeemail@example.com" data-toggle="tooltip" title="Email seller"><i class="fa fa-envelope"></i> <?php echo $uname; ?></a></span>
                            <span class="pull-right">$<?php echo $product['price']; ?></span>
                        </div>
                    </div>
            </div>
        <?php } ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <h2 class="login-panel text-muted">
                Recently Viewed
            </h2>
            <hr />
        </div>
    </div>
    <div class="row">
        <?php
        $x = 0;
        foreach ($recently_viewed_products as $rvp) {
            while ($x < 4) {
        ?>
                <div class="col-md-3">
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <?php echo '<span>' . $rvp['title'] . '</span>'; ?>
                            <span class="pull-right text-muted">
                                <?php if ($rvp['email'] == $_SESSION['email']) {
                                    echo '<a class="" href="delete.php?id=' . $product['id'] . '" data-toggle="tooltip" title="Delete item">
                                        <i class="fa fa-trash"></i></a>';
                                } ?>
                            </span>
                        </div>
                        <div class="panel-body text-center">
                            <p>
                                <?php echo
                                '<a href="product.php?id=' . $product['id'] . '">
                                        <img class="img-rounded img-thumbnail" src="products/' . $rvp['picture'] . '" />
                                    </a>';
                                ?>
                            </p>
                            <?php echo '<p class="text-muted text-justify">' . $rvp['description'] . '</p>'; ?>
                            <a class="pull-left" href="" data-toggle="tooltip" title="Downvote item">
                                <i class="fa fa-thumbs-down"></i>
                            </a>
                        </div>
                        <?php
                        $email = $rvp['email'];
                        $uname = getUsername($email);
                        ?>
                        <div class="panel-footer ">
                            <span><a href="mailto:fakeemail@example.com" data-toggle="tooltip" title="Email seller"><i class="fa fa-envelope"></i> <?php echo $uname; ?></a></span>
                            <span class="pull-right">$<?php echo $rvp['price']; ?></span>
                        </div>
                    </div>
                </div>
        <?php $x++;
            }
        } ?>

    </div>


    <div id="login" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form role="form" method="post" action="redirect.php?from=login">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title text-center">Login</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Email</label>
                            <input class="form-control" type="email" name="email">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input class="form-control" type="password" name="password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input type="submit" class="btn btn-primary" value="Login!" />
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="newItem" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form role="form" method="post" action="redirect.php?from=newItem" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title text-center">New Item</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Title</label>
                            <input class="form-control" type="text" name="title">
                        </div>
                        <div class="form-group">
                            <label>Price</label>
                            <input class="form-control" type="number" name="price">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <input class="form-control" type="text" name="desc">
                        </div>
                        <div class="form-group">
                            <label>Picture</label>
                            <input class="form-control" type="file" name="picture">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input type="submit" class="btn btn-primary" value="Post Item!" />
                    </div>
                </div><!-- /.modal-content -->
            </form>
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <div id="signup" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form role="form" method="post" action="redirect.php?from=signup">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title text-center">Sign Up</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>First Name</label>
                            <input class="form-control" type="text" name="firstname">
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <input class="form-control" type="text" name="lastname">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input class="form-control" type="email" name="email">
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input class="form-control" type="password" name="password">
                        </div>
                        <div class="form-group">
                            <label>Verify Password</label>
                            <input class="form-control" type="password" name="verify_password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input type="submit" class="btn btn-primary" value="Sign Up!" />
                    </div>
                </div><!-- /.modal-content -->
            </form>
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

</body>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script>
    $(function() {
        $('[data-toggle="tooltip"]').tooltip()
    })
</script>

</html>