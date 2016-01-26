
<?php foreach ($symbols as $symbol) : ?>
/**
 *
 */
public function <?php echo $symbol['pre'] ?>(ConnectionInterface $con = null)
{
    return true;
}

/**
 *
 */
public function <?php echo $symbol['on'] ?>(ConnectionInterface $con = null)
{
}

/**
 *
 */
public function <?php echo $symbol['post'] ?>(ConnectionInterface $con = null)
{
}

<?php endforeach; ?>
