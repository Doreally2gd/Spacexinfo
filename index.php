<?php
/* Template Name: My Project Custom Page - No Header/Footer */
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Spacexinfo - Advanced Trading & Crypto</title>
    <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/style.css" />
</head>
<body>
    <div class="animated-bg" aria-hidden="true"></div>

   
   <nav>
    <div style="display:flex; align-items:center;">
        <div class="logo">Spacexinfo</div>
    </div>

    <div class="nav-center">
        <ul class="nav-links" id="mainNav">
            <li><a href="#home">Home</a></li>
            <li><a href="#features">Features</a></li>
            <li><a href="#markets">Markets</a></li>
            <li><a href="#crypto-section">Crypto</a></li>
            <li><a href="#reviews">Reviews</a></li>
            <li><a href="#about">About</a></li>
        </ul>
    </div>

    <div style="display:flex; align-items:center; gap:1rem;">
        <div class="nav-buttons">
            <button class="btn btn-secondary"><a href="/log-in/" style="color: #00b8ff; text-decoration: none;">Sign In</a></button>
            <button class="btn btn-primary"><a href="/sign-up/" style=" color: whitesmoke; text-decoration: none;">Get Started</a></button>
        </div>

        <button class="hamburger" id="hamburger" aria-label="Toggle navigation">
            <span class="bar"></span>
        </button>
    </div>
</nav>


   <div class="mobile-nav-overlay" id="mobileOverlay" tabindex="-1" aria-hidden="true"></div>
<div class="mobile-nav" id="mobileNav" role="dialog" aria-modal="true" aria-hidden="true">
    <ul class="nav-links">
        <li><a href="#home">Home</a></li>
        <li><a href="#features">Features</a></li>
        <li><a href="#markets">Markets</a></li>
        <li><a href="#crypto-section">Crypto</a></li>
        <li><a href="#reviews">Reviews</a></li>
        <li><a href="#about">About</a></li>
    </ul>
    
    <div style="display:flex; flex-direction:column; gap:0.8rem; margin-top:1rem; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.1);">
        <button class="btn btn-primary" style="width:100%"> <a href="/sign-up/" style="text-decoration: none; color: #061026;">Get Started</a></button>
        <button class="btn btn-secondary" style="width:100%;"> <a href="/log-in/" style="text-decoration: none; color: #fff;">Sign In</a></button>
    </div>
</div>

    <main>
        <section class="hero" id="home" aria-label="hero">
            <div class="hero-content">
                <h1>Trade Smarter. Track Crypto & Markets in Real-time.</h1>
                <p>Institutional-grade tools for traders and investors. Real-time price movement simulation, market depth, and configurable watchlists — all in one place.</p>
                <div class="hero-actions">
                    <button class="btn btn-primary btn-animated"> <a href="/sign-up/" style="text-decoration: none; color: #061026;">Start Trading Now</a></button>
                    <button class="btn btn-secondary btn-animated">Try Demo Account</button>
                </div>

                <div class="hero-stats" aria-hidden="true">
                    <div class="stat">
                        <div class="stat-number">$1.2T+</div>
                        <div class="stat-label">Market Cap Tracked</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">6M+</div>
                        <div class="stat-label">Active Users</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">0.008s</div>
                        <div class="stat-label">Avg. Exec. Latency</div>
                    </div>
                </div>
            </div>

            <aside class="hero-side" aria-hidden="false">
                <div class="trading-card" aria-hidden="true">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.6rem">
                        <div style="font-weight:800">S&P 500</div>
                        <div style="color:var(--muted);font-size:.9rem">Real-time (sim)</div>
                    </div>
                    <div class="chart-placeholder">
                        <div class="chart-grid-line"></div>
                        <svg id="spChart" width="100%" height="170" viewBox="0 0 520 170" preserveAspectRatio="none"></svg>
                    </div>
                    <div class="price-display" style="margin-top:.9rem;align-items:center;gap:12px">
                        <div>
                            <div style="color:var(--muted);font-size:.9rem;margin-bottom:.2rem">S&P 500</div>
                            <div class="price" id="spPrice">$4,783.45</div>
                        </div>
                        <div class="change" id="spChange" style="background:rgba(0,255,135,0.12);color:var(--accent-start)">+2.34%</div>
                    </div>
                </div>

                <div class="crypto-panel" id="crypto" aria-label="crypto-panel" style="margin-top:8px;animation:fadeUp .5s ease both">
                    <div class="crypto-header">
                        <div>
                            <div style="font-weight:800">Crypto Watch</div>
                            <div style="color:var(--muted);font-size:.85rem">Simulated live prices — watch them drop & rise</div>
                        </div>
                        <div style="font-size:.85rem;color:var(--muted)">Updated every second</div>
                    </div>

                    <div class="market-ticker" id="coinList" style="padding-top:.25rem">
                        <!-- coin items populated by JS -->
                    </div>
                </div>
            </aside>
        </section>

        <!-- Features -->
        <section class="features" id="features">
            <h2 class="section-title">Why Choose Spacexinfo</h2>
            <p class="section-subtitle">Professional trading tools designed for both beginners and experts</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px">
                <div class="feature-card">
                    <div style="font-size:1.2rem;margin-bottom:.4rem">⚡ Lightning Execution</div>
                    <div class="muted">Direct market access with ultra-low latency routes and smart order routing.</div>
                </div>
                <div class="feature-card">
                    <div style="font-size:1.2rem;margin-bottom:.4rem">📊 Advanced Analytics</div>
                    <div class="muted">Real-time charting, indicators, and historical backtesting tools.</div>
                </div>
                <div class="feature-card">
                    <div style="font-size:1.2rem;margin-bottom:.4rem">🛡️ Bank-Level Security</div>
                    <div class="muted">256-bit encryption, multi-sig custody options, and 24/7 monitoring.</div>
                </div>
                <div class="feature-card">
                    <div style="font-size:1.2rem;margin-bottom:.4rem">💹 Crypto & Stocks</div>
                    <div class="muted">Trade both equities and cryptocurrencies with unified account management.</div>
                </div>
            </div>
        </section>

        <!-- Markets (stocks) -->
        <section class="markets" id="markets">
            <h2 class="section-title">Top Markets</h2>
            <p class="section-subtitle">Track top stocks and their daily movements</p>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px">
                <div class="feature-card">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <div>
                            <h4 style="margin-bottom:.2rem">AAPL</h4>
                            <div class="muted">Apple Inc.</div>
                        </div>
                        <div style="text-align:right">
                            <div class="price positive" id="aaplPrice">$178.23</div>
                            <div class="positive" style="font-size:.85rem">+1.45%</div>
                        </div>
                    </div>
                </div>
                <div class="feature-card">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <div>
                            <h4 style="margin-bottom:.2rem">TSLA</h4>
                            <div class="muted">Tesla, Inc.</div>
                        </div>
                        <div style="text-align:right">
                            <div class="price positive">$242.84</div>
                            <div class="positive" style="font-size:.85rem">+3.21%</div>
                        </div>
                    </div>
                </div>
                <div class="feature-card">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <div>
                            <h4 style="margin-bottom:.2rem">GOOGL</h4>
                            <div class="muted">Alphabet Inc.</div>
                        </div>
                        <div style="text-align:right">
                            <div class="price negative">$139.67</div>
                            <div class="negative" style="font-size:.85rem">-0.84%</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
<br><br>
        <!-- Crypto detailed section -->
        <section id="crypto-section" aria-label="Crypto Markets" style="padding-top:0">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <div>
                    <h2 class="section-title">Crypto Markets</h2>
                    <p class="section-subtitle">Live sim: Bitcoin, Ethereum, and top altcoins — watch their trendlines animate</p>
                </div>
                <div style="color:var(--muted)">Simulated demo data — not for trading</div>
            </div>

            <div style="margin-top:1rem;display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px" id="cryptoGrid">
                <!-- detailed coin cards populated by JS -->
            </div>
        </section>
<br><br><br><br>
        <!-- Reviews -->
        <section id="reviews">
            <h2 class="section-title">What Traders Say</h2>
            <p class="section-subtitle">Real feedback from our community</p>
            <div class="reviews-grid" aria-live="polite">
                <div class="review-card">
                    <div class="review-avatar">JS</div>
                    <div class="review-meta">
                        <div class="stars">★★★★★</div>
                        <div class="review-name">Jason S.</div>
                        <div class="review-text muted">"Fast execution and great UX. I moved from another platform and the latency improvement is night and day."</div>
                    </div>
                </div>

                <div class="review-card">
                    <div class="review-avatar" style="background:linear-gradient(135deg,#ffd54a,#ff8a5b);color:#061026">AL</div>
                    <div class="review-meta">
                        <div class="stars">★★★★☆</div>
                        <div class="review-name">Alicia L.</div>
                        <div class="review-text muted">"Excellent crypto watchlists. The simulated charts are great for practicing strategies."</div>
                    </div>
                </div>

                <div class="review-card">
                    <div class="review-avatar" style="background:linear-gradient(135deg,#7afcff,#00b8ff);color:#061026">RK</div>
                    <div class="review-meta">
                        <div class="stars">★★★★★</div>
                        <div class="review-name">R. Kumar</div>
                        <div class="review-text muted">"Top-tier analytics. Backtesting tools helped me refine my setups quickly."</div>
                    </div>
                </div>

                 <div class="review-card">
                    <div class="review-avatar" style="background:linear-gradient(135deg,#7afcff,#00b8ff);color:#061026">RK</div>
                    <div class="review-meta">
                        <div class="stars">★★★★★</div>
                        <div class="review-name">W. John</div>
                        <div class="review-text muted">"Top-tier analytics. Backtesting tools helped me refine my setups quickly."</div>
                    </div>
                </div>
            </div>
        </section>

                  
                

        <footer>
            <div class="footer-grid">
                <div>
                    <div style="font-weight:800">Spacexinfo</div>
                    <div class="muted" style="margin-top:.4rem">Powerful trading tools trusted by millions.</div>
                    <div class="social-links" style="margin-top:.6rem;display:flex;gap:.6rem">
                        <a class="social" href="#" aria-label="Website" style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,0.03);display:flex;align-items:center;justify-content:center">🔗</a>
                        <a class="social" href="https://t.me/TradeProDemo" target="_blank" rel="noopener" aria-label="Telegram" style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,0.03);display:flex;align-items:center;justify-content:center">✈️</a>
                        <a class="social" href="#" aria-label="Twitter" style="width:36px;height:36px;border-radius:8px;background:rgba(255,255,255,0.03);display:flex;align-items:center;justify-content:center">🐦</a>
                    </div>
                </div>
                <div>
                    <div style="font-weight:700;margin-bottom:.5rem">Products</div>
                    <div class="muted">Trading Platform<br/>Mobile App<br/>API Trading<br/>Demo Account</div>
                </div>
                <div>
                    <div style="font-weight:700;margin-bottom:.5rem">Company</div>
                    <div class="muted">About Us<br/>Careers<br/>Press<br/>Partners</div>
                </div>
                <div>
                    <div style="font-weight:700;margin-bottom:.5rem">Support</div>
                    <div class="muted">Help Center<br/>Contact Us<br/>Trading Guide<br/>Legal</div>
                </div>
            </div>

            <div style="text-align:center;margin-top:1rem;color:var(--muted)">© 2025 Spacexinfo. Trading involves risk and may not be suitable for all investors.</div>
        </footer>
    </main>

    

<script src="<?php echo get_stylesheet_directory_uri(); ?>/script.js"></script>
<script src="<?php echo get_stylesheet_directory_uri(); ?>/function.js"></script>
<?php wp_footer(); ?> 
</body>
</html>
<?php

// If you want the standard WordPress footer content, KEEP this line:

// get_footer(); 


?>
