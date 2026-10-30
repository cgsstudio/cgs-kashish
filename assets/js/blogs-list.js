document.addEventListener('DOMContentLoaded', () => {
  const blogGrid = document.getElementById('blogGrid');
  const paginationContainer = document.getElementById('paginationContainer');
  
  if (!blogGrid) return;

  const POSTS_PER_PAGE = 9;
  let currentPage = 1;
  let allPosts = [];

  // Fetch the blogs data from root /blog/posts.json
  fetch('/blog/posts.json')
    .then(response => {
      if (!response.ok) {
        throw new Error('Failed to load posts data');
      }
      return response.json();
    })
    .then(data => {
      allPosts = data;
      const limit = blogGrid.getAttribute('data-limit');
      if (limit) {
        allPosts = allPosts.slice(0, parseInt(limit, 10));
      }
      renderPage(currentPage);
    })
    .catch(error => {
      console.error('Error fetching blogs:', error);
      blogGrid.innerHTML = '<p>Error loading blogs. Please try again later.</p>';
    });

  function renderPage(page) {
    // Clear existing content
    blogGrid.innerHTML = '';
    
    // Calculate start and end indices
    const startIndex = (page - 1) * POSTS_PER_PAGE;
    const endIndex = Math.min(startIndex + POSTS_PER_PAGE, allPosts.length);
    const pagePosts = allPosts.slice(startIndex, endIndex);

    // Render cards
    if (pagePosts.length === 0) {
      blogGrid.innerHTML = '<p>No blogs found.</p>';
    } else {
      pagePosts.forEach(post => {
        const article = document.createElement('article');
        article.className = 'blog-card';
        const imgPath = post.image.startsWith('http') ? post.image : `/${post.image.replace(/^\/+/, '')}`;
        const linkPath = post.link.startsWith('http') ? post.link : `/${post.link.replace(/^\/+/, '')}`;
        
        article.innerHTML = `
          <a href="${linkPath}" class="blog-img-link">
            <img src="${imgPath}" alt="${post.title}" class="blog-img" loading="lazy" />
          </a>
          <div class="blog-content">
            <div class="blog-meta">
              <span class="blog-date">${post.date}</span>
            </div>
            <h3><a href="${linkPath}">${post.title}</a></h3>
            <p>${post.excerpt}</p>
            <a href="${linkPath}" class="blog-read-more">
              Read Article 
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 12h14M12 5l7 7-7 7"/>
              </svg>
            </a>
          </div>
        `;
        
        blogGrid.appendChild(article);
      });
    }

    renderPagination();
  }

  function renderPagination() {
    if (!paginationContainer) return;
    paginationContainer.innerHTML = '';

    const totalPages = Math.ceil(allPosts.length / POSTS_PER_PAGE);

    // If there's only 1 page or no pages, don't show pagination
    if (totalPages <= 1) {
      return;
    }

    // Previous Button
    const prevBtn = document.createElement('a');
    prevBtn.href = '#blogs';
    prevBtn.className = 'page-link';
    prevBtn.setAttribute('aria-label', 'Previous');
    if (currentPage === 1) {
      prevBtn.style.pointerEvents = 'none';
      prevBtn.style.opacity = '0.5';
    }
    prevBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>';
    prevBtn.addEventListener('click', (e) => {
      e.preventDefault();
      if (currentPage > 1) {
        currentPage--;
        renderPage(currentPage);
      }
    });
    paginationContainer.appendChild(prevBtn);

    // Page Numbers
    for (let i = 1; i <= totalPages; i++) {
      const pageBtn = document.createElement('a');
      pageBtn.href = '#blogs';
      pageBtn.className = `page-link ${i === currentPage ? 'active' : ''}`;
      pageBtn.textContent = i;
      pageBtn.addEventListener('click', (e) => {
        e.preventDefault();
        currentPage = i;
        renderPage(currentPage);
      });
      paginationContainer.appendChild(pageBtn);
    }

    // Next Button
    const nextBtn = document.createElement('a');
    nextBtn.href = '#blogs';
    nextBtn.className = 'page-link';
    nextBtn.setAttribute('aria-label', 'Next');
    if (currentPage === totalPages) {
      nextBtn.style.pointerEvents = 'none';
      nextBtn.style.opacity = '0.5';
    }
    nextBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>';
    nextBtn.addEventListener('click', (e) => {
      e.preventDefault();
      if (currentPage < totalPages) {
        currentPage++;
        renderPage(currentPage);
      }
    });
    paginationContainer.appendChild(nextBtn);
  }
});

