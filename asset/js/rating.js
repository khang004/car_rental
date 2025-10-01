document.addEventListener('DOMContentLoaded', function () {
    const ratingLabels = {
        1: 'Rất không hài lòng 😞',
        2: 'Không hài lòng 😕',
        3: 'Bình thường 😐',
        4: 'Hài lòng 😊',
        5: 'Rất hài lòng 😍'
    };

    // Khởi tạo rating cho tất cả container
    document.querySelectorAll('.rating-stars').forEach(container => {
        // Kiểm tra đã khởi tạo chưa
        if (container.dataset.initialized) return;
        container.dataset.initialized = 'true';

        const stars = container.querySelectorAll('.rating-star');
        const ratingText = container.parentElement.querySelector('.rating-text');

        let selected = 0;
        let isHovering = false;

        // Xử lý hover trên từng sao
        stars.forEach((star, index) => {
            const rating = parseInt(star.dataset.rating);

            // Hover vào sao
            star.addEventListener('mouseenter', (e) => {
                e.stopPropagation();
                isHovering = true;
                updateStarsHover(rating);
                showRatingText(ratingText, ratingLabels[rating]);
            });

            // Click chọn rating
            star.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                selected = rating;
                
                // Thêm animation click
                star.classList.add('clicked');
                setTimeout(() => star.classList.remove('clicked'), 300);

                // Cập nhật input radio
                const input = star.querySelector('input[type="radio"]');
                if (input) input.checked = true;

                // Cập nhật UI
                updateStarsSelected(rating);
                showRatingText(ratingText, ratingLabels[rating]);
            });
        });

        // Xử lý rời chuột khỏi container
        container.addEventListener('mouseleave', () => {
            isHovering = false;
            setTimeout(() => {
                if (!isHovering) {
                    if (selected > 0) {
                        updateStarsSelected(selected);
                        showRatingText(ratingText, ratingLabels[selected]);
                    } else {
                        resetStars();
                        hideRatingText(ratingText);
                    }
                }
            }, 5000);
        });

        // Reset khi modal đóng
        const modal = container.closest('.modal');
        if (modal) {
            modal.addEventListener('hidden.bs.modal', () => {
                selected = 0;
                resetStars();
                hideRatingText(ratingText);
                
                // Reset form
                const form = modal.querySelector('form');
                if (form) form.reset();
                
                // Reset initialized flag để có thể khởi tạo lại
                container.dataset.initialized = 'false';
            });
        }
        
        // Lắng nghe event re-initialize
        container.addEventListener('rating:reinitialize', () => {
            container.dataset.initialized = 'true';
            selected = 0;
            resetStars();
            hideRatingText(ratingText);
        });

        // Hàm cập nhật hover (tạm thời)
        function updateStarsHover(rating) {
            stars.forEach((star, index) => {
                const r = index + 1;
                star.classList.remove('selected');
                star.classList.toggle('active', r <= rating);
            });
        }

        // Hàm cập nhật selected (vĩnh viễn)
        function updateStarsSelected(rating) {
            stars.forEach((star, index) => {
                const r = index + 1;
                star.classList.remove('active');
                star.classList.toggle('selected', r <= rating);
            });
        }

        // Reset tất cả sao
        function resetStars() {
            stars.forEach(star => {
                star.classList.remove('selected', 'active');
            });
        }

        // Reset khi đóng modal
        modal.addEventListener('hidden.bs.modal', () => {
            selected = 0;
            hovered = 0;
            isMouseInside = false;
            delete container.dataset.selected;
            resetStars();
            hideRatingText(ratingText);
            
            // Reset form
            const form = modal.querySelector('form');
            if (form) form.reset();
        });



        // Hiển thị text đánh giá với animation
        function showRatingText(el, text) {
            if (!el) return;
            
            const small = el.querySelector('small');
            if (small) small.textContent = text;
            
            // Đảm bảo element visible trước khi thêm class
            el.style.display = 'block';
            el.offsetHeight; // Force reflow để đảm bảo display được áp dụng
            
            requestAnimationFrame(() => {
                el.classList.add('show');
            });
        }

        // Ẩn text đánh giá
        function hideRatingText(el) {
            if (!el) return;
            
            el.classList.remove('show');
            
            // Delay để animation hoàn thành trước khi ẩn
            setTimeout(() => {
                if (!el.classList.contains('show')) {
                    el.style.display = 'none';
                }
            }, 3000);
        }
    });
});