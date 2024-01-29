<ul class="nav nav-underline justify-content-center mb-4">
    <li class="nav-item">
      <a class="nav-link {{ url()->current() == url('/') ? 'active' : '' }}" aria-current="page" href="{{ url('/') }}">Index</a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ url()->current() == url('leveling') ? 'active' : '' }}" href="{{ url('leveling') }}">Leveling
        Calculator</a>
    </li>
    <li class="nav-item">
      <a class="nav-link {{ url()->current() == url('diadem') ? 'active' : '' }}" href="{{ url('diadem') }}">Diadem
        Optimization</a>
    </li>
    <li class="nav-item">
        <span class="nav-link disabled">・</span>
    </li>
    <li class="nav-item dropdown">
        <a id="bd-theme" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false" data-bs-display="static"
        aria-label="Toggle theme (auto)">
            <span id="bd-theme-text">Toggle theme</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bd-theme-text">
          <li>
            <button type="button" class="dropdown-item" data-bs-theme-value="light" aria-pressed="false">
                Light
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item" data-bs-theme-value="dark" aria-pressed="false">
                Dark
            </button>
          </li>
          <li>
            <button type="button" class="dropdown-item active" data-bs-theme-value="auto" aria-pressed="true">
                Auto
            </button>
          </li>
        </ul>
      </li>
  </ul>
