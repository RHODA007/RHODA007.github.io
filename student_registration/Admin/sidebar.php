<!-- sidebar.php -->
<div class="sidebar">
  <h2 class="logo">UMS</h2>
  <ul>
    <li><a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF'])=='dashboard.php' ? 'active':'' ?>"><i class="fas fa-home"></i> Dashboard</a></li>
    <li><a href="courses.php" class="<?= basename($_SERVER['PHP_SELF'])=='courses.php' ? 'active':'' ?>"><i class="fas fa-book"></i> Courses</a></li>
    <li><a href="assign_course.php" class="<?= basename($_SERVER['PHP_SELF'])=='assign_course.php' ? 'active':'' ?>"><i class="fas fa-tasks"></i> Assign Course</a></li>
    <li><a href="assignments.php" class="<?= basename($_SERVER['PHP_SELF'])=='assignments.php' ? 'active':'' ?>"><i class="fas fa-file-alt"></i> Assignments</a></li>
    <li><a href="send_message.php" class="<?= basename($_SERVER['PHP_SELF'])=='send_message.php' ? 'active':'' ?>"><i class="fas fa-envelope"></i> Messages</a></li>
    <li><a href="students.php" class="<?= basename($_SERVER['PHP_SELF'])=='students.php' ? 'active':'' ?>"><i class="fas fa-users"></i> Students</a></li>
    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
  </ul>
</div>
