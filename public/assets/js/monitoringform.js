document.addEventListener("DOMContentLoaded", function() {
    const userInputData = {
        ldaName: "",
        AuditDate1: "",
        AuditSupName: "",
        AuditorsName: "",
        AuditDate2: "",
        InvoiceID: "",
        CarrierName: "",
        ExceptionStatus: "",
        ExceptionOwner: ""
    };

    const verificationData = {
        VerIden1Comment: "",
        VerIden1Outcome: "",
        VerIden2Comment: "",
        VerIden2Outcome: ""
    };

    const processComplianceData = {
        ProCom1Comment: "",
        ProCom1Outcome: "",
        ProCom2Comment: "",
        ProCom2Outcome: "",
        ProCom3Comment: "",
        ProCom3Outcome: "",
        ProCom4Comment: "",
        ProCom4Outcome: ""
    };

    const engagementData = {
        engagement1Comment: "",
        engagement1Outcome: "",
        engagement2Comment: "",
        engagement2Outcome: "",
        engagement3Comment: "",
        engagement3Outcome: "",
        engagement4Comment: "",
        engagement4Outcome: ""
    };


    const businessAnalyticsData = {
        signCarrier: "",
        followUp: "",
        manyDays: "",
        causeIssue: "",
        impactArea: "",
        impactFactor: "",
        accountableFactors: "",
        rootCause: ""
    };

    console.log("JS LOADED");

    const elements = document.querySelectorAll("[data-trigger]");
    console.log("Choices elements:", elements.length);


    const userInputMap = {
        "lda-name": "ldaName",
        "audit-sup-name": "AuditSupName",
        "auditors-name": "AuditorsName",

    };

    const verificationMap = {
        "ver-iden-1-outcome": "VerIden1Outcome",
        "ver-iden-2-outcome": "VerIden2Outcome"
    };

    const processComplianceMap = {
        "pro-com-1-outcome": "ProCom1Outcome",
        "pro-com-2-outcome": "ProCom2Outcome",
        "pro-com-3-outcome": "ProCom3Outcome",
        "pro-com-4-outcome": "ProCom4Outcome"
    };

    const engagementMap = {
        "engagement-1-outcome": "engagement1Outcome",
        "engagement-2-outcome": "engagement2Outcome",
        "engagement-3-outcome": "engagement3Outcome",
        "engagement-4-outcome": "engagement4Outcome"
    };

    const businessAnalyticsMap = {
        "sign-carrier": "signCarrier",
        "follow-up": "followUp",
        "many-days": "manyDays",
        "cause-issue": "causeIssue",
        "impact-area": "impactArea",
        "impact-factor": "impactFactor",
        "accountable-factors": "accountableFactors",
        "root-cause": "rootCause"
    };


    const inputUserInputMap = {
        "audit-date1": "AuditDate1",
        "audit-date2": "AuditDate2",
        "invoice-id": "InvoiceID",
        "carrier-name": "CarrierName",
        "exception-status": "ExceptionStatus",
        "exception-owner": "ExceptionOwner"
    };

    const textareaVerificationMap = {
        "ver-iden-1-comment": "VerIden1Comment",
        "ver-iden-2-comment": "VerIden2Comment"
    };

    const textareaProcessComplianceMap = {
        "pro-com-1-comment": "ProCom1Comment",
        "pro-com-2-comment": "ProCom2Comment",
        "pro-com-3-comment": "ProCom3Comment",
        "pro-com-4-comment": "ProCom4Comment"
    };

    const textareaEngagementMap = {
        "engagement-1-comment": "engagement1Comment",
        "engagement-2-comment": "engagement2Comment",
        "engagement-3-comment": "engagement3Comment",
        "engagement-4-comment": "engagement4Comment"
    };

    const textareaBusinessAnalyticsMap = {
        "many-days": "manyDays"
    };


    const resultDisplay = {

        verificationData: {
            "ver-iden-1-outcome": "res-vid-1",
            "ver-iden-2-outcome": "res-vid-2"
        },
        processCompliance: {
            "pro-com-1-outcome": "res-pc-1",
            "pro-com-2-outcome": "res-pc-2",
            "pro-com-3-outcome": "res-pc-3",
            "pro-com-4-outcome": "res-pc-4"
        },
        engagement: {
            "engagement-1-outcome": "res-ce-1",
            "engagement-2-outcome": "res-ce-2",
            "engagement-3-outcome": "res-ce-3",
            "engagement-4-outcome": "res-ce-4"
        }
    };

    function updateResultDisplay(section, field, value) {
        const mapping = resultDisplay[section];
        if (mapping && mapping[field]) {
            const elementId = mapping[field];
            const element = document.getElementById(elementId);

            if (!element) return;

            const number = Number(value);

            // Define value sets
            const successValues = [10, 15, 100];
            const warningValues = [8, 5];

            // Reset classes
            element.classList.remove(
                "text-success",
                "text-warning",
                "text-danger"
            );

            if (successValues.includes(number)) {
                element.classList.add("text-success");
                element.textContent = number + "%";
            } else if (warningValues.includes(number)) {
                element.classList.add("text-warning");
                element.textContent = number + "%";
            } else if (number === 0) {
                element.classList.add("text-danger");
                element.textContent = number + "%";
            } else {
                element.textContent = "NA";
            }
        }
    }

    function sumOutcomes(data, debug = false) {
        let total = 0;

        for (const key in data) {
            if (key.endsWith('Outcome')) {
                const value = Number(data[key]);
                if (!isNaN(value)) {
                    total += value;
                } else if (debug) {
                    console.warn(`Invalid value for ${key}:`, data[key]);
                }
            }
        }
        return total;
    }

    const verificationTotalElement = 'total-verification';
    const processComplianceTotalElement = 'total-process-compliance';
    const engagementTotalElement = 'total-engagement';

    function updateTotalDisplay(elementId, total) {
        const element = document.getElementById(elementId);
        if (!element) return;

        element.style.transition = 'width 0.5s ease';
        element.style.width = total + '%';
        element.setAttribute('aria-valuenow', total);
        element.textContent = total + '%';

        element.classList.remove('bg-success', 'bg-warning', 'bg-danger');

        if (total < 40) {
            element.classList.add('bg-danger');
        } else if (total < 70) {
            element.classList.add('bg-warning');
        } else {
            element.classList.add('bg-success');
        }
    }

    function verificationCalculation(value) {
        const total = 200;
        return (value / total) * 100;
    }

    function generalCalculation(value) {
        const total = 50;
        return (value / total) * 100;
    }



    function OverAllScoreCalcu(value) {
        const total = 100;
        return (value / total) * 100;
    }

    elements.forEach(el => {
        new Choices(el);

        el.addEventListener("change", function() {

            const data = {
                value: this.value,
                label: this.options[this.selectedIndex]?.text || "",
                name: this.name
            };

            if (userInputMap[this.name]) {
                userInputData[userInputMap[this.name]] = this.value;

            }

            if (verificationMap[this.name]) {
                verificationData[verificationMap[this.name]] = this.value;
                updateResultDisplay("verificationData", this.name, this.value);

            }

            if (processComplianceMap[this.name]) {
                processComplianceData[processComplianceMap[this.name]] = this.value;
                updateResultDisplay("processCompliance", this.name, this.value);
            }

            if (engagementMap[this.name]) {
                engagementData[engagementMap[this.name]] = this.value;
                updateResultDisplay("engagement", this.name, this.value);
            }


            if (businessAnalyticsMap[this.name]) {
                businessAnalyticsData[businessAnalyticsMap[this.name]] = this.value;
            }
            console.clear();
            console.log(data);
            console.log("User Input:", userInputData);
            console.log("Verification:", verificationData);
            console.log("Process Compliance:", processComplianceData);
            console.log("Engagement:", engagementData);
            console.log("Business Analytics:", businessAnalyticsData);

            const totalverificationData = sumOutcomes(verificationData);
            const verificationPercentage = verificationCalculation(totalverificationData);
            updateTotalDisplay(verificationTotalElement, verificationPercentage);

            const totalProcessComplianceData = sumOutcomes(processComplianceData);
            const processCompliancePercentage = generalCalculation(totalProcessComplianceData);
            updateTotalDisplay(processComplianceTotalElement, processCompliancePercentage);

            const totalEngagementData = sumOutcomes(engagementData);
            const engagementPercentage = generalCalculation(totalEngagementData);
            updateTotalDisplay(engagementTotalElement, engagementPercentage);


            console.log("Totals - Verification:", verificationPercentage, "Process Compliance:", processCompliancePercentage, "Engagement:", engagementPercentage);
            // const overallTotal = totalverificationData + totalProcessComplianceData + totalEngagementData;
            // const overallPercentage = OverAllScoreCalcu(overallTotal);


            console.log("verificationPercentage:", verificationPercentage);

            if (verificationPercentage < 99) {
                document.getElementById("overall-score").textContent = "0%";
            } else {
                const overallTotal = totalProcessComplianceData + totalEngagementData;
                const overallPercentage = OverAllScoreCalcu(overallTotal);
                console.log("Overall Score Percentage:", overallPercentage);
                document.getElementById("overall-score").textContent = overallPercentage + "%";
            }

        });
    });

    document.querySelectorAll("input, textarea").forEach(el => {

        el.addEventListener("input", function() {

            if (inputUserInputMap[this.name]) {
                userInputData[inputUserInputMap[this.name]] = this.value;
            }

            if (textareaVerificationMap[this.name]) {
                verificationData[textareaVerificationMap[this.name]] = this.value;
            }

            if (textareaProcessComplianceMap[this.name]) {
                processComplianceData[textareaProcessComplianceMap[this.name]] = this.value;
            }

            if (textareaEngagementMap[this.name]) {
                engagementData[textareaEngagementMap[this.name]] = this.value;
            }
            if (textareaBusinessAnalyticsMap[this.name]) {
                businessAnalyticsData[textareaBusinessAnalyticsMap[this.name]] = this.value;
            }

            console.clear();
            console.log("User Input:", userInputData);
            console.log("Verification:", verificationData);
            console.log("Process Compliance:", processComplianceData);
            console.log("Engagement:", engagementData);
            console.log("Business Analytics:", businessAnalyticsData);
        });

    });


    document.querySelectorAll(".datepicker-humanfd").forEach(el => {
        flatpickr(el, {
            altInput: true,
            altFormat: "F j, Y",
            dateFormat: "Y-m-d"
        });
    });

    function showWelcomeAlert(message, icon) {

        Swal.fire({
            position: "top-end",
            icon: icon,
            title: message,
            showConfirmButton: !1,
            timer: 2500
        })

        if (icon === "success") {
            setTimeout(() => {
                window.location.reload();
            }, 2600);
        }
    }

    document.getElementById("submitBtn")?.addEventListener("click", function(e) {
        e.preventDefault(); // ✅ prevent form reload

        const auditBy = document.getElementById("audit-by").value;
        userInputData["AuditBy"] = auditBy;

        const payload = {
            userInputData,
            verificationData,
            processComplianceData,
            engagementData,
            businessAnalyticsData
        };

        console.log("FINAL PAYLOAD:", payload);

        fetch("/api/audits", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content")
                },
                body: JSON.stringify(payload)
            })
            .then(async res => {
                const data = await res.json();
                if (!res.ok) throw data;
                return data;
            })
            .then(data => {
                console.log("SUCCESS:", data);
                // alert(`Audit created! ID: ${data.audit_id}`);
                // // ✅ Refresh page AFTER success
                showWelcomeAlert("Audit created successfully!", "success");
                
            })
            .catch(err => {
                showWelcomeAlert("Failed to submit audit. Check console.", "error");
                console.error("API ERROR:", err);
                // alert("Failed to submit audit. Check console.");
            });
    });


});