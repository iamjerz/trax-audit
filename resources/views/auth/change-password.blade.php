<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    @include('partials.header')
    <div class="authentication-bg min-vh-100">
        <div class="bg-overlay bg-light"></div>
        <div class="container">
            <div class="d-flex flex-column min-vh-100 px-3 pt-4">
                <div class="row justify-content-center my-auto">
                    <div class="col-md-8 col-lg-6 col-xl-5">

                        <div class="mb-4 pb-2">
                            <a href="/login" class="d-block auth-logo">
                                <img src="https://www.traxtech.com/hubfs/build_assets/trax-core/251/js_client_assets/assets/logo-hwTUqwwd.svg" alt="" height="30" class="auth-logo-dark me-start">
                                <img src="https://www.traxtech.com/hubfs/build_assets/trax-core/251/js_client_assets/assets/logo-hwTUqwwd.svg" alt="" height="30" class="auth-logo-light me-start">
                            </a>
                        </div>

                        <div class="card">
                            <div class="card-body p-4">
                                <div class="text-center mt-2">
                                    <h5>Update Your Password</h5>
                                    <p class="text-muted">For security, you must change the default password before continuing.</p>
                                </div>
                                <div class="p-2 mt-4">

                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0 ps-3">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('password.update') }}">
                                        @csrf

                                        <div class="mb-3">
                                            <label class="form-label" for="password">New Password</label>
                                            <div class="position-relative auth-pass-inputgroup input-custom-icon">
                                                <span class="bx bx-lock-alt"></span>
                                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password" required>
                                                <button type="button" class="btn btn-link position-absolute h-100 end-0 top-0" id="password-addon">
                                                    <i class="mdi mdi-eye-outline font-size-18 text-muted"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label" for="password_confirmation">Confirm New Password</label>
                                            <div class="position-relative auth-pass-inputgroup input-custom-icon">
                                                <span class="bx bx-lock-alt"></span>
                                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Re-enter new password" required>
                                                <button type="button" class="btn btn-link position-absolute h-100 end-0 top-0">
                                                    <i class="mdi mdi-eye-outline font-size-18 text-muted"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <button class="btn btn-primary w-100 waves-effect waves-light" type="submit">Update Password</button>
                                        </div>
                                    </form>

                                    <div class="mt-4 text-center">
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-link text-muted p-0">Sign out</button>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div><!-- end col -->
                </div><!-- end row -->
            </div>
        </div><!-- end container -->
    </div>
    @include('partials.script')
    <script>
        // Show / hide password toggle (works for every .auth-pass-inputgroup on the page)
        document.querySelectorAll('.auth-pass-inputgroup').forEach(function (group) {
            var btn = group.querySelector('button');
            var input = group.querySelector('input');
            if (!btn || !input) return;

            btn.addEventListener('click', function () {
                input.type = input.type === 'password' ? 'text' : 'password';
                var icon = btn.querySelector('i');
                if (icon) {
                    icon.classList.toggle('mdi-eye-outline');
                    icon.classList.toggle('mdi-eye-off-outline');
                }
            });
        });
    </script>
    </body>
</html>
