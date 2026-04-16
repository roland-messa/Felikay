<section class="py-24 bg-[#F3F3F3]">
  <div class="max-w-[1200px] mx-auto px-6">
    <div class="text-center mb-16" data-aos="fade-up">
      <h4 class="uppercase text-[10px] tracking-[0.5em] font-medium text-stone-400 mb-4">Paroles de clients</h4>
      <div class="w-10 h-[1px] bg-stone-300 mx-auto"></div>
    </div>

    <div class="swiper swiper-testimonials overflow-hidden" data-aos="fade-up" data-aos-delay="200">
      <div class="swiper-wrapper">

        <div class="swiper-slide text-center px-4 md:px-24">
          <p class="font-serif text-2xl md:text-4xl leading-relaxed text-[#1A1A1A] italic">
            « Chemise en denim blanc parfaitement ajustée, plus de denim blanc que prévu, souple et incroyablement douce. »
          </p>
          <div class="mt-10">
            <span class="block uppercase text-[10px] tracking-[0.4em] font-bold text-gray-500">Jonathan Lutala</span>
          </div>
        </div>

        <div class="swiper-slide text-center px-4 md:px-24">
          <p class="font-serif text-2xl md:text-4xl leading-relaxed text-[#1A1A1A] italic">
            « Une expérience d'achat unique. La qualité des tissus dépasse mes attentes. Felikay est devenue ma référence. »
          </p>
          <div class="mt-10">
            <span class="block uppercase text-[10px] tracking-[0.4em] font-bold text-gray-500">Brean Ntajala</span>
          </div>
        </div>

        <div class="swiper-slide text-center px-4 md:px-24">
          <p class="font-serif text-2xl md:text-4xl leading-relaxed text-[#1A1A1A] italic">
            « Le service client est aussi impeccable que leurs coupes. Je recommande vivement la collection d'été. »
          </p>
          <div class="mt-10">
            <span class="block uppercase text-[10px] tracking-[0.4em] font-bold text-gray-500">Messa Roland</span>
          </div>
        </div>

      </div>

      <div class="swiper-pagination-dots mt-16 flex justify-center space-x-3"></div>
    </div>
  </div>
</section>

<style>
  /* Style spécifique pour la pagination des témoignages */
  .swiper-pagination-dots .swiper-pagination-bullet {
    width: 6px;
    height: 6px;
    background: #A8A29E;
    /* stone-400 */
    opacity: 0.5;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    margin: 0 4px;
    border-radius: 50%;
  }

  .swiper-pagination-dots .swiper-pagination-bullet-active {
    background: #1A1A1A;
    width: 30px;
    opacity: 1;
    border-radius: 10px;
  }
</style>

<script>
  // Initialisation du Swiper Testimonials
  document.addEventListener('DOMContentLoaded', () => {
    const swiperTestimonials = new Swiper(".swiper-testimonials", {
      slidesPerView: 1,
      spaceBetween: 50,
      loop: true,
      grabCursor: true,
      autoplay: {
        delay: 6000,
        disableOnInteraction: false,
      },
      pagination: {
        el: ".swiper-pagination-dots",
        clickable: true,
      },
      speed: 1000,
      effect: 'slide'
    });
  });
</script>