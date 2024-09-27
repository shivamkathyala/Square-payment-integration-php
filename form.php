<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Form</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <script type="text/javascript" src="https://web.squarecdn.com/v1/square.js"></script>
    <script>
        
        const appId = '';
        const locationId = '';

        async function initializeCard(payments) {
            const card = await payments.card();
            await card.attach('#card-container');
            return card;
        }

        async function createPayment(token, formData) {
            const body = JSON.stringify({
                locationId,
                sourceId: token,
                idempotencyKey: window.crypto.randomUUID(),
                formData // Including the form data in the request body
            });

            const paymentResponse = await fetch('https://brandclever.in/developer/squareapp/payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body,
            });

            if (paymentResponse.ok) {
                const jsonResponse = await paymentResponse.json();
                
                if (jsonResponse.status === "failure"){
                    alert('Payment failed, please try again.');
                    location.reload();
                }
                if(jsonResponse.status === "success"){
                    const statusContainer = document.getElementById('payment-status-container');
                    statusContainer.style.display = 'block';
                    statusContainer.innerText = 'Payment Successful! Redirecting...';
                    redirectToThankYouPage();
                }          
            } else {
                const errorBody = await paymentResponse.text();
                throw new Error(errorBody);
            }
        }
        function redirectToThankYouPage() {
            window.location.href = "https://app.funnel-preview.com/for_domain/www.mjgfitnessmarketing.co.uk/pp-thank-you?updated_at=9a65e8669dcce0706fd8b5263b3df57dv2&track=0&preview=true";
        }

        function getFormData() {
    // Getting the form fields
    const fullNameField = document.getElementById('full-name');
    const emailField = document.getElementById('email');
    const phoneNumberField = document.getElementById('phone-number');
    const companyNameField = document.getElementById('company-name');
    const streetAddressField = document.getElementById('street-address');
    const cityField = document.getElementById('city');
    const countyField = document.getElementById('county');
    const postcodeField = document.getElementById('postcode');
    const countryField = document.getElementById('country');

    const priceField = document.getElementById('package_price');

    // Function to remove error highlight on keyup
    function removeErrorHighlight(event) {
        event.target.classList.remove('error-border');
    }

    // Attach keyup event listeners
    fullNameField.addEventListener('keyup', removeErrorHighlight);
    emailField.addEventListener('keyup', removeErrorHighlight);
    phoneNumberField.addEventListener('keyup', removeErrorHighlight);
    companyNameField.addEventListener('keyup', removeErrorHighlight);
    streetAddressField.addEventListener('keyup', removeErrorHighlight);
    cityField.addEventListener('keyup', removeErrorHighlight);
    countyField.addEventListener('keyup', removeErrorHighlight);
    postcodeField.addEventListener('keyup', removeErrorHighlight);
    countryField.addEventListener('change', removeErrorHighlight);


    let isValid = true;

    // Validation checks
    if (!fullNameField.value.trim()) {
        fullNameField.classList.add('error-border');
        isValid = false;
    }

    if (!emailField.value.trim() || !validateEmail(emailField.value.trim())) {
        emailField.classList.add('error-border');
        isValid = false;
    }

    if (!phoneNumberField.value.trim() || !validatePhoneNumber(phoneNumberField.value.trim())) {
        phoneNumberField.classList.add('error-border');
        isValid = false;
    }

    if (!companyNameField.value.trim()) {
        companyNameField.classList.add('error-border');
        isValid = false;
    }

    if (!streetAddressField.value.trim()) {
        streetAddressField.classList.add('error-border');
        isValid = false;
    }

    if (!countyField.value.trim()) {
        countyField.classList.add('error-border');
        isValid = false;
    }

    if (!postcodeField.value) {
        postcodeField.classList.add('error-border');
        isValid = false;
    }

    if (!cityField.value) {
        cityField.classList.add('error-border');
        isValid = false;
    }

    if (!countryField.value) {
        countryField.classList.add('error-border');
        isValid = false;
    }


    // If any field is invalid, return false to stop form submission
    if (!isValid) {
        return false;
    }

    
    // Return the validated form data
    return {
        fullName: fullNameField.value.trim(),
        email: emailField.value.trim(),
        phoneNumber: phoneNumberField.value.trim(),
        companyName: companyNameField.value.trim(),
        streetAddress: streetAddressField.value.trim(),
        city: cityField.value.trim(),
        county: countyField.value.trim(),
        country: countryField.value,
        price: priceField.value
    };
}

// Helper function to validate email
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Helper function to validate phone number
function validatePhoneNumber(phoneNumber) {
    const phoneRegex = /^\+?[0-9\s\-()]{10,20}$/;
    return phoneRegex.test(phoneNumber);
}
        async function tokenize(paymentMethod) {
            const tokenResult = await paymentMethod.tokenize();
            if (tokenResult.status === 'OK') {
                return tokenResult.token;
            } else {
                let errorMessage = `Tokenization failed-status: ${tokenResult.status}`;
                if (tokenResult.errors) {
                    errorMessage += ` and errors: ${JSON.stringify(tokenResult.errors)}`;
                }
                throw new Error(errorMessage);
            }
        }

        function displayPaymentResults(status) {
            const statusContainer = document.getElementById('payment-status-container');
            if (status === 'SUCCESS') {
                statusContainer.classList.remove('is-failure');
                statusContainer.classList.add('is-success');
            } else {
                statusContainer.classList.remove('is-success');
                statusContainer.classList.add('is-failure');
            }

            statusContainer.style.visibility = 'visible';
        }

        document.addEventListener('DOMContentLoaded', async function () {
            if (!window.Square) {
                throw new Error('Square.js failed to load properly');
            }
            const payments = window.Square.payments(appId, locationId);
            let card;
            try {
                card = await initializeCard(payments);
            } catch (e) {
                console.error('Initializing Card failed', e);
                return;
            }

            async function handlePaymentMethodSubmission(event, paymentMethod) {
                event.preventDefault();
                const formData = getFormData(); // Collect form data
                try {
                    // Disable the submit button while awaiting tokenization and payment
                    cardButton.disabled = true;
                    cardButton.classList.add('button-faded');
                    const token = await tokenize(paymentMethod);
                    const paymentResults = await createPayment(token, formData); 
                    displayPaymentResults('SUCCESS');
                    

                    console.debug('Payment Success', paymentResults);
                } catch (e) {
                    cardButton.disabled = false;
                    displayPaymentResults('FAILURE');
                    console.error(e.message);
                }
            }
            // Payment button functionality
            const cardButton = document.getElementById('card-button');
            cardButton.addEventListener('click', async function (event) {
                await handlePaymentMethodSubmission(event, card);

                if (cardButton.disabled) {
                    cardButton.classList.add('button-faded');
                } else {
                    cardButton.classList.remove('button-faded');
                }
            });
        });
    </script>

</head>

<body>
    <div class="fuulbodtpendding">

        <div class="form-header">
            <img src="img/WHT_LG.png" alt="MJG Marketing Logo" class="logo">
            <div class="fa_tag_icn_text">
            <i class="fa fa-tags"></i>
            <h2>CHOOSE YOUR SERVICE</h2>
            </div>
            <div class="billing-container">

                <div class="billing-info-header">

                    <p>Item</p>

                    <div>
                        <p>Price</p>
                    </div>
                </div>
                <hr class="divider">
                <div class="item-row">
                    <div class="item-select">
                        <input type="radio" id="item1" name="item" checked>
                        <label for="item1">Professional Package</label>
                    </div>
                    <div class="item-price">
                        <span id="package_price">£1.00</span><p>every month</p>
                    </div>
                </div>
            </div>
            <div class="billing_info_main">
            <i class="fa fa-credit-card-alt" style="font-size:14px"></i>
                <h2> BIlling Information</h2>
            </div>
        </div>


        <div class="form-container">

            <form class="leftrhight" id="payment-form">
                <div class="form-group">
                    <label for="full-name">FULL NAME</label>
                    <input type="text" id="full-name" placeholder="Full Name..." required>
                </div>
                <div class="form-group">
                    <label for="email">EMAIL ADDRESS</label>
                    <input type="email" id="email" placeholder="Email Address..." required>
                </div>
                <div class="form-group">
                    <label for="phone-number">PHONE NUMBER</label>
                    <input type="tel" id="phone-number" placeholder="Phone Number..." required>
                </div>
                <div class="form-group">
                    <label for="company-name">COMPANY NAME</label>
                    <input type="text" id="company-name" placeholder="Company Name..." required>
                </div>
                <div class="form-group">
                    <label for="street-address">STREET ADDRESS</label>
                    <input type="text" id="street-address" placeholder="Street Address..." required>
                </div>
                <div class="oneline online_flex">
                    <div class="form-group">
                        <label for="city">CITY</label>
                        <input class="widhtinput" type="text" id="city" placeholder="City Name..." required>
                    </div>
                    <div class="form-group">
                        <label for="county">COUNTY</label>
                        <input class="widhtinput" type="text" id="county" placeholder="County..." required>
                    </div>
                </div>
                <div class="oneline online_flex">
                    <div class="form-group">
                        <label for="city">Post Code</label>
                        <input class="widhtinput" type="text" id="postcode" placeholder="Post Code..." required>
                    </div>
                    <div class="form-group">
                        <label for="country">COUNTRY</label>
                        <select class="widhtinput" id="country">
                            <option value="">Select Country</option><option value="">------------------------------</option><option value="US">United States</option><option value="CA">Canada</option><option value="GB">United Kingdom</option><option value="IE">Ireland</option><option value="AU">Australia</option><option value="NZ">New Zealand</option><option value="">------------------------------</option><option value="AF">Afghanistan</option><option value="AX">Aland Islands</option><option value="AL">Albania</option><option value="DZ">Algeria</option><option value="AS">American Samoa</option><option value="AD">Andorra</option><option value="AO">Angola</option><option value="AI">Anguilla</option><option value="AQ">Antarctica</option><option value="AG">Antigua and Barbuda</option><option value="AR">Argentina</option><option value="AM">Armenia</option><option value="AW">Aruba</option><option value="AU">Australia</option><option value="AT">Austria</option><option value="AZ">Azerbaijan</option><option value="BS">Bahamas</option><option value="BH">Bahrain</option><option value="BD">Bangladesh</option><option value="BB">Barbados</option><option value="BY">Belarus</option><option value="BE">Belgium</option><option value="BZ">Belize</option><option value="BJ">Benin</option><option value="BM">Bermuda</option><option value="BT">Bhutan</option><option value="BO">Bolivia</option><option value="BQ">Bonaire, Saint Eustatius and Saba </option><option value="BA">Bosnia and Herzegovina</option><option value="BW">Botswana</option><option value="BV">Bouvet Island</option><option value="BR">Brazil</option><option value="IO">British Indian Ocean Territory</option><option value="VG">British Virgin Islands</option><option value="BN">Brunei</option><option value="BG">Bulgaria</option><option value="BF">Burkina Faso</option><option value="BI">Burundi</option><option value="KH">Cambodia</option><option value="CM">Cameroon</option><option value="CA">Canada</option><option value="CV">Cape Verde</option><option value="KY">Cayman Islands</option><option value="CF">Central African Republic</option><option value="TD">Chad</option><option value="CL">Chile</option><option value="CN">China</option><option value="CX">Christmas Island</option><option value="CC">Cocos Islands</option><option value="CO">Colombia</option><option value="KM">Comoros</option><option value="CK">Cook Islands</option><option value="CR">Costa Rica</option><option value="HR">Croatia</option><option value="CU">Cuba</option><option value="CW">Curacao</option><option value="CY">Cyprus</option><option value="CZ">Czech Republic</option><option value="CD">Democratic Republic of the Congo</option><option value="DK">Denmark</option><option value="DJ">Djibouti</option><option value="DM">Dominica</option><option value="DO">Dominican Republic</option><option value="TL">East Timor</option><option value="EC">Ecuador</option><option value="EG">Egypt</option><option value="SV">El Salvador</option><option value="GQ">Equatorial Guinea</option><option value="ER">Eritrea</option><option value="EE">Estonia</option><option value="ET">Ethiopia</option><option value="FK">Falkland Islands</option><option value="FO">Faroe Islands</option><option value="FJ">Fiji</option><option value="FI">Finland</option><option value="FR">France</option><option value="GF">French Guiana</option><option value="PF">French Polynesia</option><option value="TF">French Southern Territories</option><option value="GA">Gabon</option><option value="GM">Gambia</option><option value="GE">Georgia</option><option value="DE">Germany</option><option value="GH">Ghana</option><option value="GI">Gibraltar</option><option value="GR">Greece</option><option value="GL">Greenland</option><option value="GD">Grenada</option><option value="GP">Guadeloupe</option><option value="GU">Guam</option><option value="GT">Guatemala</option><option value="GG">Guernsey</option><option value="GN">Guinea</option><option value="GW">Guinea-Bissau</option><option value="GY">Guyana</option><option value="HT">Haiti</option><option value="HM">Heard Island and McDonald Islands</option><option value="HN">Honduras</option><option value="HK">Hong Kong</option><option value="HU">Hungary</option><option value="IS">Iceland</option><option value="IN">India</option><option value="ID">Indonesia</option><option value="IR">Iran</option><option value="IQ">Iraq</option><option value="IE">Ireland</option><option value="IM">Isle of Man</option><option value="IL">Israel</option><option value="IT">Italy</option><option value="CI">Ivory Coast</option><option value="JM">Jamaica</option><option value="JP">Japan</option><option value="JE">Jersey</option><option value="JO">Jordan</option><option value="KZ">Kazakhstan</option><option value="KE">Kenya</option><option value="KI">Kiribati</option><option value="XK">Kosovo</option><option value="KW">Kuwait</option><option value="KG">Kyrgyzstan</option><option value="LA">Laos</option><option value="LV">Latvia</option><option value="LB">Lebanon</option><option value="LS">Lesotho</option><option value="LR">Liberia</option><option value="LY">Libya</option><option value="LI">Liechtenstein</option><option value="LT">Lithuania</option><option value="LU">Luxembourg</option><option value="MO">Macao</option><option value="MK">Macedonia</option><option value="MG">Madagascar</option><option value="MW">Malawi</option><option value="MY">Malaysia</option><option value="MV">Maldives</option><option value="ML">Mali</option><option value="MT">Malta</option><option value="MH">Marshall Islands</option><option value="MQ">Martinique</option><option value="MR">Mauritania</option><option value="MU">Mauritius</option><option value="YT">Mayotte</option><option value="MX">Mexico</option><option value="FM">Micronesia</option><option value="MD">Moldova</option><option value="MC">Monaco</option><option value="MN">Mongolia</option><option value="ME">Montenegro</option><option value="MS">Montserrat</option><option value="MA">Morocco</option><option value="MZ">Mozambique</option><option value="MM">Myanmar</option><option value="NA">Namibia</option><option value="NR">Nauru</option><option value="NP">Nepal</option><option value="NL">Netherlands</option><option value="NC">New Caledonia</option><option value="NZ">New Zealand</option><option value="NI">Nicaragua</option><option value="NE">Niger</option><option value="NG">Nigeria</option><option value="NU">Niue</option><option value="NF">Norfolk Island</option><option value="KP">North Korea</option><option value="MP">Northern Mariana Islands</option><option value="NO">Norway</option><option value="OM">Oman</option><option value="PK">Pakistan</option><option value="PW">Palau</option><option value="PS">Palestinian Territory</option><option value="PA">Panama</option><option value="PG">Papua New Guinea</option><option value="PY">Paraguay</option><option value="PE">Peru</option><option value="PH">Philippines</option><option value="PN">Pitcairn</option><option value="PL">Poland</option><option value="PT">Portugal</option><option value="PR">Puerto Rico</option><option value="QA">Qatar</option><option value="CG">Republic of the Congo</option><option value="RE">Reunion</option><option value="RO">Romania</option><option value="RU">Russia</option><option value="RW">Rwanda</option><option value="BL">Saint Barthelemy</option><option value="SH">Saint Helena</option><option value="KN">Saint Kitts and Nevis</option><option value="LC">Saint Lucia</option><option value="MF">Saint Martin</option><option value="PM">Saint Pierre and Miquelon</option><option value="VC">Saint Vincent and the Grenadines</option><option value="WS">Samoa</option><option value="SM">San Marino</option><option value="ST">Sao Tome and Principe</option><option value="SA">Saudi Arabia</option><option value="SN">Senegal</option><option value="RS">Serbia</option><option value="SC">Seychelles</option><option value="SL">Sierra Leone</option><option value="SG">Singapore</option><option value="SX">Sint Maarten</option><option value="SK">Slovakia</option><option value="SI">Slovenia</option><option value="SB">Solomon Islands</option><option value="SO">Somalia</option><option value="ZA">South Africa</option><option value="GS">South Georgia and the South Sandwich Islands</option><option value="KR">South Korea</option><option value="SS">South Sudan</option><option value="ES">Spain</option><option value="LK">Sri Lanka</option><option value="SD">Sudan</option><option value="SR">Suriname</option><option value="SJ">Svalbard and Jan Mayen</option><option value="SZ">Swaziland</option><option value="SE">Sweden</option><option value="CH">Switzerland</option><option value="SY">Syria</option><option value="TW">Taiwan</option><option value="TJ">Tajikistan</option><option value="TZ">Tanzania</option><option value="TH">Thailand</option><option value="TG">Togo</option><option value="TK">Tokelau</option><option value="TO">Tonga</option><option value="TT">Trinidad and Tobago</option><option value="TN">Tunisia</option><option value="TR">Turkey</option><option value="TM">Turkmenistan</option><option value="TC">Turks and Caicos Islands</option><option value="TV">Tuvalu</option><option value="VI">U.S. Virgin Islands</option><option value="UG">Uganda</option><option value="UA">Ukraine</option><option value="AE">United Arab Emirates</option><option value="GB">United Kingdom</option><option value="US">United States</option><option value="UM">United States Minor Outlying Islands</option><option value="UY">Uruguay</option><option value="UZ">Uzbekistan</option><option value="VU">Vanuatu</option><option value="VA">Vatican</option><option value="VE">Venezuela</option><option value="VN">Vietnam</option><option value="WF">Wallis and Futuna</option><option value="EH">Western Sahara</option><option value="YE">Yemen</option><option value="ZM">Zambia</option><option value="ZW">Zimbabwe</option>
                        </select>
                    </div>
                </div>

                <div id="card-container"></div>


                <div class="seth2tag">
                    <i class="fa fa-archive" style="font-size:24px"></i>
                    <h2> ORDER DETAIL</h2>
    
                </div>
                <div class="billing">
                    <p>Price</p>
                </div>
                <div class="professional_package">
                    <p>Professional Package</p>
                    <p>£1.00</p>
                </div>
                <div class="order-container">
                    <div id="payment-status-container" style="display:none;"></div>
                    <button class="complete-order-button" id="card-button" type="button">Complete Order</button>
                    <img src="img/credit-only.png">
                </div>


            </form>
        </div>

        
</body>

</html>