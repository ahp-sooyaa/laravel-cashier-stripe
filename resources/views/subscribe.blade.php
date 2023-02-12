<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Subscription') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('subscribe.post') }}" method="POST" id="payment-form" class="w-1/2" data-secret="{{ $intent->client_secret }}">
                        @csrf

                        <div class="mb-3 text-xs">
                            <input type="radio" name="plan" id="normal_plan" value="price_1M7gHYK3AEGA3BHp12rBozb2" checked>
                            <label for="normal_plan">Normal plan</label>
                            <input type="radio" name="plan" id="premium_plan" value="price_1M7gG2K3AEGA3BHp0uJLFoY6">
                            <label for="premium_plan">Premium plan</label>
                        </div>

                        <input id="card-holder-name" type="text" placeholder="Card Holder Name" class="border-gray-300 rounded-md text-xs mb-3">

                        <div id="card-element" class="border shadow p-2 rounded-md">
                            <!-- Elements will create input elements here -->
                        </div>

                        <!-- We'll put the error messages in this element -->
                        <div id="card-errors" role="alert" class="text-red-500 text-sm mt-1"></div>

                        <x-primary-button class="mt-3">Submit Payment</x-primary-button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script type="module">
            // Set your publishable key: remember to change this to your live publishable key in production
            // See your keys here: https://dashboard.stripe.com/apikeys
            // console.log(import.meta); need to define 'type="module"' otherwise import.meta show syntax error
            const stripe = Stripe('pk_test_51JnarjK3AEGA3BHp6n9Urmu60LR702RMXQVWzOj3uN7KY6IT9HIQLn7uoFi7rzO3b41glf7kRJSQJ1VBAyElLg1100etmH6ltD');

            // Set up Stripe.js and Elements to use in checkout form
            const elements = stripe.elements();
            const style = {
                base: {
                    iconColor: '#32325d',
                    color: "#32325d",
                    fontWeight: '300',
                    fontSize: '14px',
                    fontSmoothing: 'antialiased',
                },
                invalid: {
                    iconColor: '#e92121',
                    color: '#e92121',
                },
            };

            const card = elements.create("card", { style: style });
            card.mount("#card-element");

            card.on('change', ({error}) => {
                let displayError = document.getElementById('card-errors');
                if (error) {
                    displayError.textContent = error.message;
                } else {
                    displayError.textContent = '';
                }
            });

            // handle form submission
            const form = document.getElementById('payment-form');
            const cardHolderName = document.getElementById('card-holder-name');
            const clientSecret = form.dataset.secret;

            form.addEventListener('submit', async (ev) => {
                ev.preventDefault();
                // If the client secret was rendered server-side as a data-secret attribute
                // on the <form> element, you can retrieve it here by calling `form.dataset.secret`
                const { setupIntent, error } = await stripe.confirmCardSetup(clientSecret, {
                    payment_method: {
                        card: card,
                        billing_details: {
                            name: cardHolderName.value,
                        }
                    }
                });

                if (error) {
                    // Show error to your customer (for example, insufficient funds)
                    console.log(error.message);
                    let displayError = document.getElementById('card-errors');
                    displayError.textContent = error.message;
                } else {
                    // The payment has been processed!
                    console.log(setupIntent);
                    if (setupIntent.status === 'succeeded') {
                        // Show a success message to your customer
                        // There's a risk of the customer closing the window before callback
                        // execution. Set up a webhook or plugin to listen for the
                        // payment_intent.succeeded event that handles any business critical
                        // post-payment actions.
                        const hiddenInput = document.createElement('input');
                        hiddenInput.setAttribute('type', 'hidden');
                        hiddenInput.setAttribute('name', 'payment_method');
                        hiddenInput.setAttribute('value', setupIntent.payment_method);

                        form.appendChild(hiddenInput);

                        // submit form
                        form.submit();
                    }
                }
            });
        </script>
    @endpush
</x-app-layout>