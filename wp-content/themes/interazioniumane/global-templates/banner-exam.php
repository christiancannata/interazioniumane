<section class="banner apply">
  <div class="banner__content">
    <h4 class="banner__text apply">Iscriviti!</h4>
    <p class="banner__subtitle">Compila il form, partecipa all'esame.</p>
    <div class="banner__form">
      <?php
        $word = "II livello";
        $mystring = get_the_title();
        if (str_contains($mystring, $word)) {
          echo do_shortcode('[contact-form-7 title="Esami II livello"]');
        } else{
            echo do_shortcode('[contact-form-7 title="Esami"]');
        }

        ?>

    </div>
  </div>
</section>
