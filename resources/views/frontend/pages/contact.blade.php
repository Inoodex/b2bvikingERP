@extends('layouts.frontend')
@section('title', 'Contact Us')

@section('content')
    <div class="bg-slate-50 py-10 sm:py-12">
        <div class="mx-auto flex max-w-6xl flex-col gap-10 px-4 sm:px-6 lg:px-8">
            <!-- Header Section -->
            <section class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
                <div class="pointer-events-none absolute -right-16 -top-24 h-56 w-56 rounded-full bg-slate-100 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-[#efe7d9] blur-3xl"></div>

                <div class="relative z-10 max-w-3xl">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Get In Touch</p>
                    <h1 class="mt-3 text-3xl font-bold text-slate-900 sm:text-4xl">Contact Us</h1>
                    <p class="mt-4 text-base leading-relaxed text-slate-600">
                        We'd love to hear from you! Whether you have a question about our products, need help with an order, 
                        or want to discuss a partnership, our team is here to help.
                    </p>
                </div>
            </section>

            <!-- Contact Info & Form Section -->
            <section class="grid gap-8 lg:grid-cols-2">
                <!-- Contact Information -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
                    <h2 class="text-xl font-bold text-slate-900">Get In Touch</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        Fill out the form and our team will get back to you within 24 hours.
                    </p>

                    <div class="mt-8 space-y-6">
                        <!-- Phone -->
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M20.01 15.38c-1.23 0-2.42-.2-3.53-.56a.977.977 0 00-1.01.24l-2.2 2.2a15.161 15.161 0 01-6.59-6.59l2.2-2.21a.96.96 0 00.25-1A11.36 11.36 0 018.56 3.98c.98-.36 1.96-.6 2.95-.6 1.23 0 2.42.2 3.53.56a.977.977 0 001.01-.24l2.2-2.2a.977.977 0 00-.25-1.01c-1.23-.36-2.52-.56-3.85-.56-3.59 0-6.9 2.22-8.66 5.44a12.377 12.377 0 00-.48 4.43c0 3.59 2.22 6.9 5.44 8.66a12.38 12.38 0 004.43.48c1.43-.02 2.82-.29 4.14-.81l.22.22a.977.977 0 01-.24 1.01c-.92.36-1.91.56-2.94.56z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">Phone</p>
                                <p class="mt-1 text-base font-semibold text-slate-900">{{ $settings->phone ?? '+4553713518' }}</p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M3 8l6.003 6a2 2 0 002.826 0L21 8v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                                    <path d="M21 8l-7.174-5.826A2 2 0 0012.64 2H11.36a2 2 0 00-1.666.826L3 8"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">Email</p>
                                <p class="mt-1 text-base font-semibold text-slate-900">{{ $settings->contact_email ?? 'contact@yourcompany.com' }}</p>
                            </div>
                        </div>

                        <!-- Address -->
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-rose-50 text-rose-500">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path fill-rule="evenodd" d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">Address</p>
                                <p class="mt-1 text-base font-semibold text-slate-900">{{ $settings->address ?? '123 Business Street, Suite 100, New York, NY 10001' }}</p>
                            </div>
                        </div>

                        <!-- Working Hours -->
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-50 text-amber-500">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12 2a1 1 0 011 1v1h3a1 1 0 010 2h-1v9a2 2 0 01-2 2H7a2 2 0 01-2-2V6H4a1 1 0 010-2h3V3a1 1 0 011-1zm0 10a1 1 0 011 1v3h1a1 1 0 110 2H7a1 1 0 110-2h1v-3a1 1 0 011-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-500">Working Hours</p>
                                <p class="mt-1 text-base font-semibold text-slate-900">Mon - Fri: 9:00 AM - 6:00 PM<br>Sat - Sun: Closed</p>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Links -->
                    {{-- <div class="mt-8 border-t border-slate-200 pt-8">
                        <p class="text-sm font-medium text-slate-500">Follow Us</p>
                        <div class="mt-4 flex gap-4">
                            <a href="#" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition-colors hover:bg-blue-600 hover:text-white">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                </svg>
                            </a>
                            <a href="#" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition-colors hover:bg-indigo-600 hover:text-white">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </a>
                            <a href="#" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition-colors hover:bg-blue-700 hover:text-white">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"/>
                                </svg>
                            </a>
                            <a href="#" class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-600 transition-colors hover:bg-green-600 hover:text-white">
                                <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.068 2.068 3.279 4.811 3.641 7.533 1.015 1.404 1.672 3.104 1.745 4.859l.021.112c0 6.627-5.373 12-12 12h-1.305c-6.627 0-12-5.373-12-12 0-5.488 3.528-10.16 8.348-11.611-.498-1.09-1.027-2.336-1.365-3.663-.533-2.071.072-4.544 1.912-6.12 1.771-1.51 4.619-1.51 6.39 0 1.839 1.561 2.445 4.049 2.537 5.946 2.413 6.228-1.19 12.926-7.291 15.313-5.342 2.081-11.74 1.582-15.865-1.328-4.268-3.006-5.826-8.008-4.352-12.873.405-1.331.978-2.56 1.69-3.645l-.081-.299c-.405-1.578.279-3.175 1.652-3.608 1.373-.433 3.108.153 4.473 1.608 1.365 1.455 1.612 3.604.616 4.863-.996 1.259-2.625 2.209-4.252 2.929-.649.286-1.344.514-2.073.635l-.113-.006c-1.629-.278-3.224-.893-4.343-1.917-1.478-1.356-2.289-3.423-2.289-5.545 0-3.924 3.155-7.104 7.045-7.104 1.954 0 3.773.798 5.107 2.094 1.213 1.181 1.975 2.794 2.149 4.682l.012.138c.162 1.382-.109 2.807-.789 3.987l-.002.006c-.914 1.573-2.624 2.612-4.461 2.812l-.108-.009c-1.953-.224-3.864-.921-5.238-2.059-1.521-1.231-2.553-2.987-2.893-4.829l-.022-.121c-.21-.928.102-1.872.856-2.517.755-.646 1.823-.738 2.664-.31l.075.038c1.422 1.005 3.016 1.482 4.612 1.482 1.279 0 2.513-.333 3.611-.968 1.098-.634 1.865-1.569 2.192-2.656.405-1.341.155-2.87-.685-4.029-1.684-2.323-4.384-3.077-6.806-2.149-2.422.929-4.111 3.165-4.131 5.625-.02 2.46 1.443 4.753 3.757 5.891 1.395.687 2.915.987 4.406.823l.134-.015c1.467.204 2.834.907 3.85 1.976 1.016 1.069 1.492 2.492 1.338 3.89l-.014.113c.177.914-.088 1.861-.736 2.632-.648.771-1.606 1.173-2.66 1.119l-.13-.007c-1.304-.071-2.533-.601-3.442-1.486-.908-.885-1.404-2.057-1.392-3.285l.001-.027c.022 1.419-.359 2.825-1.072 3.958-.713 1.133-1.741 1.968-2.89 2.348-1.149.381-2.37.322-3.416-.165z"/>
                                </svg>
                            </a>
                        </div>
                    </div> --}}
                </div>

                <!-- Contact Form -->
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-10">
                    <h2 class="text-xl font-bold text-slate-900">Send Us a Message</h2>
                    <p class="mt-2 text-sm text-slate-600">
                        Have a question? Fill out the form below and we'll get back to you.
                    </p>

                    <div id="contact-alerts" class="space-y-3"></div>

                    @if (session('contact_success'))
                        <div class="alert-auto-hide rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {{ session('contact_success') }}
                        </div>
                    @endif
                    @if (session('contact_error'))
                        <div class="alert-auto-hide rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            {{ session('contact_error') }}
                        </div>
                    @endif

                    <form id="contact-form" action="{{ route('contact.submit') }}" method="POST" class="mt-8 space-y-6">
                        @csrf
                        <div class="grid gap-6 sm:grid-cols-2">
                            <!-- First Name -->
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-slate-700">First Name</label>
                                <input type="text" name="first_name" id="first_name" 
                                    class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                    placeholder="John" value="{{ old('first_name') }}" required>
                                <p class="mt-1 text-xs text-rose-600" data-error-for="first_name">
                                    @error('first_name'){{ $message }}@enderror
                                </p>
                            </div>

                            <!-- Last Name -->
                            <div>
                                <label for="last_name" class="block text-sm font-medium text-slate-700">Last Name</label>
                                <input type="text" name="last_name" id="last_name" 
                                    class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                    placeholder="Doe" value="{{ old('last_name') }}" required>
                                <p class="mt-1 text-xs text-rose-600" data-error-for="last_name">
                                    @error('last_name'){{ $message }}@enderror
                                </p>
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                            <input type="email" name="email" id="email" 
                                class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                placeholder="john@example.com" value="{{ old('email') }}" required>
                            <p class="mt-1 text-xs text-rose-600" data-error-for="email">
                                @error('email'){{ $message }}@enderror
                            </p>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-700">Phone Number</label>
                            <input type="tel" name="phone" id="phone" 
                                class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                placeholder="+1 (555) 000-0000" value="{{ old('phone') }}">
                            <p class="mt-1 text-xs text-rose-600" data-error-for="phone">
                                @error('phone'){{ $message }}@enderror
                            </p>
                        </div>

                        <!-- Subject -->
                        <div>
                            <label for="subject" class="block text-sm font-medium text-slate-700">Subject</label>
                            <input type="text" name="subject" id="subject" 
                                class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                placeholder="How can we help you?" value="{{ old('subject') }}" required>
                            <p class="mt-1 text-xs text-rose-600" data-error-for="subject">
                                @error('subject'){{ $message }}@enderror
                            </p>
                        </div>

                        <!-- Message -->
                        <div>
                            <label for="message" class="block text-sm font-medium text-slate-700">Message</label>
                            <textarea name="message" id="message" rows="5" 
                                class="mt-2 block w-full rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                placeholder="Write your message here..." required>{{ old('message') }}</textarea>
                            <p class="mt-1 text-xs text-rose-600" data-error-for="message">
                                @error('message'){{ $message }}@enderror
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" id="contact-submit"
                            class="w-full rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Send Message
                        </button>
                    </form>
                </div>
            </section>

            <!-- Map Section -->
            {{-- <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-2">
                <div class="aspect-video w-full overflow-hidden rounded-xl bg-slate-100">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3022.183967123456!2d-73.985654!3d40.748817!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zNDDCsDQ0JzUwLjciTiA3M8KwNTknMTIuNiJX!5e0!3m2!1sen!2sus!4v1234567890"
                        width="100%" 
                        height="100%" 
                        style="border:0;" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </section> --}}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert-auto-hide');
            if (!alerts.length) return;

            setTimeout(() => {
                alerts.forEach((alert) => {
                    alert.style.transition = 'opacity 0.3s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 350);
                });
            }, 5000);
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('contact-form');
            const submitBtn = document.getElementById('contact-submit');
            const alertsContainer = document.getElementById('contact-alerts');

            if (!form) return;

            const clearErrors = () => {
                document.querySelectorAll('[data-error-for]').forEach((el) => {
                    el.textContent = '';
                });
            };

            const showAlert = (type, message) => {
                if (!alertsContainer) return;
                const color = type === 'success'
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                    : 'border-rose-200 bg-rose-50 text-rose-700';
                const wrapper = document.createElement('div');
                wrapper.className = `alert-auto-hide rounded-lg border px-4 py-3 text-sm ${color}`;
                wrapper.textContent = message;
                alertsContainer.appendChild(wrapper);

                setTimeout(() => {
                    wrapper.style.transition = 'opacity 0.3s ease';
                    wrapper.style.opacity = '0';
                    setTimeout(() => wrapper.remove(), 350);
                }, 5000);
            };

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                clearErrors();

                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Sending...';

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new FormData(form)
                    });

                    if (response.status === 422) {
                        const data = await response.json();
                        if (data.errors) {
                            Object.keys(data.errors).forEach((field) => {
                                const target = document.querySelector(`[data-error-for="${field}"]`);
                                if (target) {
                                    target.textContent = data.errors[field][0] ?? '';
                                }
                            });
                        }
                        showAlert('error', 'Please fix the highlighted fields and try again.');
                        return;
                    }

                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        showAlert('error', data.message || 'Sorry, we could not send your message right now.');
                        return;
                    }

                    form.reset();
                    showAlert('success', data.message || 'Thanks! Your message has been sent. Our team will reply soon.');
                } catch (err) {
                    showAlert('error', 'Network error. Please try again.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        });
    </script>
@endsection
