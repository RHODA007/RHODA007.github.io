<footer class="footer" style="background:#f5f5f5; color:#111; padding:50px 0; font-family:'Segoe UI', sans-serif;">
  <div class="container d-flex flex-wrap justify-content-between">

    <!-- About -->
    <div class="mb-4" style="flex:1 1 250px;">
      <h5 style="font-weight:700;">RhodaX Tech University</h5>
      <p>Shaping the future of technology through innovation and education.</p>
    </div>

    <!-- Quick Links -->
    <div class="mb-4" style="flex:1 1 150px;">
      <h6 style="font-weight:600;">Quick Links</h6>
      <ul style="list-style:none; padding:0;">
        <li><a href="index.php" style="color:#111; text-decoration:none;">Home</a></li>
        <li><a href="about.php" style="color:#111; text-decoration:none;">About</a></li>
        <li><a href="courses.php" style="color:#111; text-decoration:none;">Courses</a></li>
        <li><a href="contact.php" style="color:#111; text-decoration:none;">Contact</a></li>
        <li><a href="events.php" style="color:#111; text-decoration:none;">Events</a></li>
        <li><a href="instructors.php" style="color:#111; text-decoration:none;">Instructors</a></li>
      </ul>
    </div>

    <!-- Contact -->
    <div class="mb-4" style="flex:1 1 200px;">
      <h6 style="font-weight:600;">Contact Us</h6>
      <p><i class="fas fa-envelope"></i> info@rhodaxtech.edu</p>
      <p><i class="fas fa-phone"></i> +234 815 518 3456</p>
      <p><i class="fas fa-map-marker-alt"></i> 66, igbe road, Auchi, Benin City 312101, Edo</p>
    </div>

    <!-- Newsletter -->
    <div class="mb-4" style="flex:1 1 250px;">
      <h6 style="font-weight:600;">Newsletter</h6>
      <p>Subscribe to get the latest updates and news.</p>
      <form id="newsletter-form" class="d-flex">
        <input type="email" placeholder="Your email" required style="flex:1; padding:6px 10px; border:1px solid #ccc; border-radius:4px 0 0 4px;">
        <button type="submit" style="border:none; background:#007bff; color:#fff; padding:6px 15px; border-radius:0 4px 4px 0;">Subscribe</button>
      </form>
      <p id="newsletter-msg" style="margin-top:5px; font-size:0.9rem; color:#555;"></p>
    </div>

    <!-- Social -->
    <div class="mb-4" style="flex:1 1 150px;">
      <h6 style="font-weight:600;">Follow Us</h6>
      <div class="d-flex gap-2 mt-2">
        <a href="#" style="color:#111; font-size:1.2rem;"><i class="fab fa-facebook-f"></i></a>
        <a href="#" style="color:#111; font-size:1.2rem;"><i class="fab fa-twitter"></i></a>
        <a href="#" style="color:#111; font-size:1.2rem;"><i class="fab fa-instagram"></i></a>
        <a href="#" style="color:#111; font-size:1.2rem;"><i class="fab fa-linkedin-in"></i></a>
      </div>
    </div>

  </div>

  <div class="footer-bottom text-center mt-4" style="padding-top:20px; border-top:1px solid #ddd; font-size:0.9rem; color:#555;">
    <p>&copy; <?= date('Y') ?> RhodaX Tech University. All Rights Reserved.</p>
  </div>
</footer>
