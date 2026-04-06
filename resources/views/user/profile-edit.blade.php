@extends('theme-vinahentai::layout.main')

@section('body')
    <div class="mx-auto flex w-full max-w-[951px] flex-col items-start gap-6 px-4 py-6 sm:px-6 lg:px-0"
        data-profile-edit-root
        data-profile-update-url="{{ route('api.user.profile.update') }}"
        data-password-update-url="{{ route('api.user.password.update') }}">
        <div data-rht-toaster="" style="position: fixed; z-index: 9999; inset: 16px; pointer-events: none;"></div>

        <div class="flex w-full flex-col items-start gap-6">
            <h1 class="text-txt-primary font-sans text-2xl leading-9 font-semibold sm:text-3xl" style="text-shadow: rgba(182, 25, 255, 0.59) 0px 0px 4px;">Sửa hồ sơ</h1>
            <div class="bg-bgc-layer1 border-bd-default w-full rounded-xl border p-4 shadow-[0px_4px_4px_0px_rgba(0,0,0,0.25)] sm:p-6">
                <form id="profile-form" class="flex flex-col gap-6" enctype="multipart/form-data">
                    @csrf
                    <div class="grid gap-3 sm:grid-cols-12 sm:items-center">
                        <div class="flex items-center gap-1.5 sm:col-span-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user text-txt-secondary h-4 w-4" aria-hidden="true">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg><label class="text-txt-primary font-sans text-base font-semibold" for="profile-name">Username</label></div>
                        <div class="space-y-2 sm:col-span-9"><input id="profile-name" class="bg-bgc-layer2 border-bd-default text-txt-primary focus:border-lav-500 w-full rounded-xl border px-3 py-2.5 font-sans text-base font-medium outline-none " placeholder="1-15 ký tự, chỉ chữ cái và số" type="text" value="{{ old('name', $user->name) }}" name="name" required maxlength="15" pattern="[a-zA-Z0-9]+" autocomplete="username">
                            <div class="text-txt-secondary text-xs">1-15 ký tự, chỉ được sử dụng chữ cái (a-z, A-Z) và số (0-9)</div>
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-12 sm:items-center">
                        <div class="flex items-center gap-1.5 sm:col-span-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail text-txt-secondary h-4 w-4" aria-hidden="true">
                                <path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"></path>
                                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                            </svg><label class="text-txt-primary font-sans text-base font-semibold">Email</label></div>
                        <div class="sm:col-span-9"><input readonly class="bg-bgc-layer2 border-bd-default text-txt-secondary w-full cursor-not-allowed rounded-xl border px-3 py-2.5 font-sans text-base font-medium opacity-60 outline-none" type="email" value="{{ $user->email }}" name="email" tabindex="-1" aria-readonly="true"></div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-12 sm:items-start">
                        <div class="flex items-start gap-1.5 sm:col-span-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-camera text-txt-secondary h-4 w-4 sm:mt-0.5" aria-hidden="true">
                                <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"></path>
                                <circle cx="12" cy="13" r="3"></circle>
                            </svg><label class="text-txt-primary font-sans text-base font-semibold">Ảnh đại diện</label></div>
                        <div class="flex w-full flex-col gap-4 sm:col-span-9">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start">
                                <div class="border-bd-default bg-bgc-layer2 relative flex h-32 w-32 shrink-0 items-center justify-center overflow-hidden rounded-xl border sm:h-36 sm:w-36 aspect-square">
                                    <img id="avatar-preview" src="{{ $user->avatar ?: '' }}" alt="" class="{{ $user->avatar ? 'block' : 'hidden' }} h-full w-full object-cover">
                                    <span id="avatar-preview-empty" class="{{ $user->avatar ? 'hidden' : 'text-txt-secondary text-xs px-2 text-center' }}">Chưa có ảnh</span>
                                </div>
                                <div class="flex flex-col gap-2">
                                    <label for="image-upload" class="inline-flex w-max cursor-pointer items-center justify-center gap-1.5 rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-4 py-3 shadow-lg transition-opacity hover:opacity-90">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload h-5 w-5 text-black" aria-hidden="true">
                                            <path d="M12 3v12"></path>
                                            <path d="m17 8-5-5-5 5"></path>
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        </svg>
                                        <span class="font-sans text-sm font-semibold text-black">Chọn ảnh</span>
                                    </label>
                                    <input id="image-upload" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden" type="file">
                                    <p class="text-txt-secondary text-xs">JPEG, PNG, GIF, Webp — tối đa 2MB.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-12 sm:items-start">
                        <div class="flex items-start gap-1.5 sm:col-span-3"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text text-txt-secondary h-4 w-4 sm:mt-1" aria-hidden="true">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
                                <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
                                <path d="M10 9H8"></path>
                                <path d="M16 13H8"></path>
                                <path d="M16 17H8"></path>
                            </svg><label class="text-txt-primary font-sans text-base font-semibold" for="profile-bio">Giới thiệu</label></div>
                        <div class="sm:col-span-9"><textarea id="profile-bio" name="bio" placeholder="Nhập giới thiệu vào đây..." class="bg-bgc-layer2 border-bd-default text-txt-primary placeholder:text-txt-secondary focus:border-lav-500 min-h-60 w-full resize-none rounded-xl border px-3 py-2.5 font-sans text-base font-medium outline-none">{{ old('bio', $user->bio) }}</textarea></div>
                    </div>
                </form>
            </div>
        </div>
        <div class="flex w-full flex-col items-center gap-2.5 sm:items-end">
            <button type="submit" form="profile-form" id="profile-submit-btn" class="flex w-full items-center justify-center gap-2.5 rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-4 py-3 shadow-[0px_4px_8.899999618530273px_0px_rgba(196,69,255,0.25)] transition-opacity disabled:opacity-50 sm:w-52">
                <span class="text-center font-sans text-sm font-semibold text-black">Lưu</span>
            </button>
        </div>

        <div class="bg-bgc-layer1 border-bd-default w-full rounded-xl border p-4 shadow-[0px_4px_4px_0px_rgba(0,0,0,0.25)] sm:p-6">
            <button type="button" data-toggle-password class="flex w-full items-center gap-2 text-left" aria-expanded="false" aria-controls="password-change-form">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-key-round text-txt-secondary h-5 w-5" aria-hidden="true">
                    <path d="M2.586 17.414A2 2 0 0 0 2 18.828V21a1 1 0 0 0 1 1h3a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h1a1 1 0 0 0 1-1v-1a1 1 0 0 1 1-1h.172a2 2 0 0 0 1.414-.586l.814-.814a6.5 6.5 0 1 0-4-4z"></path>
                    <circle cx="16.5" cy="7.5" r=".5" fill="currentColor"></circle>
                </svg>
                <span class="text-txt-primary font-sans text-lg font-semibold">Đổi mật khẩu</span>
                <svg data-password-chevron class="ml-auto h-5 w-5 text-txt-secondary transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <form id="password-change-form" class="mt-4 hidden space-y-4" autocomplete="off">
                @csrf
                <div class="grid gap-3 sm:grid-cols-12 sm:items-center">
                    <label class="text-txt-primary font-sans text-sm font-semibold sm:col-span-3" for="current_password">Mật khẩu hiện tại</label>
                    <div class="sm:col-span-9">
                        <input id="current_password" required class="bg-bgc-layer2 border-bd-default text-txt-primary focus:border-lav-500 w-full rounded-xl border px-3 py-2.5 font-sans text-base font-medium outline-none" placeholder="Nhập mật khẩu hiện tại" type="password" name="current_password" autocomplete="current-password">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-12 sm:items-center">
                    <label class="text-txt-primary font-sans text-sm font-semibold sm:col-span-3" for="new_password">Mật khẩu mới</label>
                    <div class="sm:col-span-9">
                        <input id="new_password" required class="bg-bgc-layer2 border-bd-default text-txt-primary focus:border-lav-500 w-full rounded-xl border px-3 py-2.5 font-sans text-base font-medium outline-none" placeholder="Ít nhất 6 ký tự" type="password" name="password" autocomplete="new-password" minlength="6">
                    </div>
                </div>
                <div class="grid gap-3 sm:grid-cols-12 sm:items-center">
                    <label class="text-txt-primary font-sans text-sm font-semibold sm:col-span-3" for="password_confirmation">Xác nhận mật khẩu</label>
                    <div class="sm:col-span-9">
                        <input id="password_confirmation" required class="bg-bgc-layer2 border-bd-default text-txt-primary focus:border-lav-500 w-full rounded-xl border px-3 py-2.5 font-sans text-base font-medium outline-none" placeholder="Nhập lại mật khẩu mới" type="password" name="password_confirmation" autocomplete="new-password" minlength="6">
                    </div>
                </div>
                <button type="submit" id="password-submit-btn" class="self-end rounded-xl bg-gradient-to-b from-[#DD94FF] to-[#D373FF] px-6 py-2.5 text-sm font-semibold text-black shadow-[0px_4px_8.9px_0px_rgba(196,69,255,0.25)] transition-opacity hover:opacity-90 disabled:opacity-50">Đổi mật khẩu</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const root = document.querySelector("[data-profile-edit-root]");
            if (!root) return;

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") || "";
            const profileUrl = root.getAttribute("data-profile-update-url") || "";
            const passwordUrl = root.getAttribute("data-password-update-url") || "";

            function firstValidationMessage(errors) {
                if (!errors || typeof errors !== "object") return null;
                const keys = Object.keys(errors);
                if (!keys.length) return null;
                const arr = errors[keys[0]];
                return Array.isArray(arr) && arr.length ? String(arr[0]) : null;
            }

            function toastOk(msg) {
                if (typeof FuiToast !== "undefined") FuiToast.success(msg);
                else alert(msg);
            }

            function toastErr(msg) {
                if (typeof FuiToast !== "undefined") FuiToast.error(msg);
                else alert(msg);
            }

            const profileForm = document.getElementById("profile-form");
            const profileBtn = document.getElementById("profile-submit-btn");
            const fileInput = document.getElementById("image-upload");
            const avatarPreview = document.getElementById("avatar-preview");
            const avatarEmpty = document.getElementById("avatar-preview-empty");

            fileInput?.addEventListener("change", function () {
                const f = fileInput.files && fileInput.files[0];
                if (!f || !avatarPreview || !avatarEmpty) return;
                const r = new FileReader();
                r.onload = function () {
                    avatarPreview.src = r.result;
                    avatarPreview.classList.remove("hidden");
                    avatarEmpty.classList.add("hidden");
                };
                r.readAsDataURL(f);
            });

            profileForm?.addEventListener("submit", async function (e) {
                e.preventDefault();
                if (!profileUrl) return;
                const fd = new FormData(profileForm);
                profileBtn && (profileBtn.disabled = true);
                try {
                    const res = await fetch(profileUrl, {
                        method: "POST",
                        headers: {
                            Accept: "application/json",
                            "X-CSRF-TOKEN": csrf,
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: fd,
                        credentials: "same-origin",
                    });
                    const data = await res.json().catch(function () {
                        return {};
                    });
                    if (!res.ok) {
                        const msg = firstValidationMessage(data.errors) || data.message || "Không lưu được hồ sơ.";
                        toastErr(msg);
                        return;
                    }
                    toastOk(data.message || "Đã lưu.");
                    if (data.user && data.user.avatar && avatarPreview) {
                        avatarPreview.src = data.user.avatar;
                        avatarPreview.classList.remove("hidden");
                        avatarEmpty && avatarEmpty.classList.add("hidden");
                    }
                } catch (err) {
                    toastErr("Lỗi mạng, thử lại sau.");
                } finally {
                    profileBtn && (profileBtn.disabled = false);
                }
            });

            const togglePw = root.querySelector("[data-toggle-password]");
            const pwForm = document.getElementById("password-change-form");
            const chevron = root.querySelector("[data-password-chevron]");
            const pwBtn = document.getElementById("password-submit-btn");

            togglePw?.addEventListener("click", function () {
                if (!pwForm) return;
                pwForm.classList.toggle("hidden");
                const open = !pwForm.classList.contains("hidden");
                togglePw.setAttribute("aria-expanded", open ? "true" : "false");
                if (chevron) {
                    chevron.style.transform = open ? "rotate(180deg)" : "";
                }
            });

            pwForm?.addEventListener("submit", async function (e) {
                e.preventDefault();
                if (!passwordUrl) return;
                const fd = new FormData(pwForm);
                pwBtn && (pwBtn.disabled = true);
                try {
                    const res = await fetch(passwordUrl, {
                        method: "POST",
                        headers: {
                            Accept: "application/json",
                            "X-CSRF-TOKEN": csrf,
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: fd,
                        credentials: "same-origin",
                    });
                    const data = await res.json().catch(function () {
                        return {};
                    });
                    if (!res.ok) {
                        const msg = firstValidationMessage(data.errors) || data.message || "Đổi mật khẩu thất bại.";
                        toastErr(msg);
                        return;
                    }
                    toastOk(data.message || "Đã đổi mật khẩu.");
                    pwForm.reset();
                } catch (err) {
                    toastErr("Lỗi mạng, thử lại sau.");
                } finally {
                    pwBtn && (pwBtn.disabled = false);
                }
            });
        })();
    </script>
@endpush
