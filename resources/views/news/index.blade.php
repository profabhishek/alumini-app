@extends ('layouts.app')

@section ('title', 'News & Updates')

@push ('styles')
    <link rel="stylesheet" href="{{ asset('css/news.css') }}" />
@endpush

@section ('content')
    <section class="news-page">
        {{-- HERO --}}
        <section class="news-hero">
            <div class="container">
                <span class="news-badge"> ICCR Alumni Newsroom </span>

                <h1 class="news-title">News & Updates</h1>

                <p class="news-subtitle">Stay informed with the latest alumni stories, announcements, cultural initiatives and global activities.</p>
            </div>
        </section>

        {{-- SEARCH --}}
        <section class="news-toolbar">
            <div class="container">
                <form class="news-search-form">
                    <input type="text" placeholder="Search news articles..." />

                    <button type="submit">Search</button>
                </form>
            </div>
        </section>

        {{-- FEATURED NEWS --}}
        <section class="featured-news">
            <div class="container">
                <article class="featured-card">
                    <div class="featured-image">
                        <img
                            src="https://picsum.photos/1200/700"
                            alt="Featured News"
                        />
                    </div>

                    <div class="featured-content">
                        <span class="featured-tag"> Featured Story </span>

                        <h2>
                            India Africa Forum Summit Strengthens Alumni
                            Relations Across Continents
                        </h2>

                        <p>A landmark gathering brought together alumni leaders, scholars and cultural ambassadors to strengthen collaboration and cultural diplomacy.</p>

                        <a href="#" class="news-read-btn"> Read Full Story </a>
                    </div>
                </article>
            </div>
        </section>

        {{-- NEWS GRID --}}
        <section class="news-section">
            <div class="container">
                <div class="news-grid">
                    @for ($i = 1; $i <= 9; $i++)
                        <article class="news-card">
                            <div class="news-image-wrap">
                                <img
                                    src="https://picsum.photos/600/400?random={{ $i }}"
                                    alt="News"
                                    class="news-image"
                                />
                            </div>

                            <div class="news-content">
                                <div class="news-meta">
                                    <span> May 2026 </span>

                                    <span> 4 min read </span>
                                </div>

                                <h3 class="news-card-title">
                                    ICCR Alumni Community Expands Global
                                    Engagement
                                </h3>

                                <p class="news-excerpt">Discover how alumni chapters around the world are creating opportunities for collaboration, networking and cultural exchange.</p>

                                <a href="#" class="news-link"> Read More → </a>
                            </div>
                        </article>

                    @endfor
                </div>
            </div>
        </section>

        {{-- PAGINATION --}}
        <section class="news-pagination">
            <div class="container">
                <div class="pagination-demo">
                    <button><</button>

                    <button class="active">1</button>

                    <button>2</button>

                    <button>3</button>

                    <button>></button>
                </div>
            </div>
        </section>
    </section>

@endsection
