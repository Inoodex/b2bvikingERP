# Phase 2 Plan

This is the comprehensive implementation plan for the entire **Phase 2**, directly matched with the steps outlined in your `daily_roadmap.md`. 

---

## Step 1: RFQ, Quotations, and Comparison Statements (CS) [Days 11 - 13]
**Status:** 🟢 **100% Completed**
*   **Completed Work:** 
    *   Successfully built the RFQ generation.
    *   Built the Email-based Vendor Quotation System (public link mechanism).
    *   **PDF Attachment:** System automatically generates an RFQ PDF and attaches it to the Vendor Invitation Email.
    *   **Comparison Statement Engine:** Built a dynamic CS Matrix UI to compare all submitted quotations side-by-side.
    *   Implemented Auto Currency Conversion to calculate the lowest price (L1 bidder) in the Base Currency accurately.
    *   **Approval Workflows Integration:** The generated CS is automatically routed to the dynamic `Approval Workflows` engine for management review and approval.

## Step 2: PO Upgrades, Proforma Invoices, and LC Details [Days 14 - 16]
**Status:** 🟡 **IN PROGRESS (Current Focus)**
*   **PO Generation (2.9):** Auto-draft Purchase Orders (POs) directly from the Approved CS, including support for "Split POs" (awarding different items to different vendors).
*   **PO Approval (2.10):** Multi-level approval integration for the newly generated PO.
*   **Edit / Cancel PO (2.11):** Functionality to modify or void a PO.
*   **Email PO to Supplier (2.12):** Generate PDF and email it directly.
*   **Foreign Purchase — PO (2.14):** Multi-currency support and exchange rate locking.
*   **Proforma Invoice (PI) (2.15):** Create a module to upload, track, and attach vendor PI details against the generated PO.
*   **Letter of Credit (LC) (2.17):** Build a comprehensive LC module to track LC Opening Banks, LC Numbers, Amendments, and associated LC Expenses.

## Step 3: Shipment, SIT, and GRN with Quality Control [Days 17 - 19]
**Status:** 🔴 **Pending**
*   **Shipment Details (2.13, 2.18):** Track transport documents (BL/AWB), Port of Loading/Discharge, Vessel Tracking, ETD, and ETA.
*   **Stock-in-Transit (SIT) (2.19):** Develop financial integrations to calculate and record SIT values while goods are on the water/in the air.
*   **Good Receipt Note (GRN) & QC (2.20):** Build a dynamic GRN checklist for warehouse teams to perform Quality Control (QC) and record actual received quantities versus ordered quantities.

## Step 4: Landed Cost Allocation Engine & Vendor Returns [Days 20 - 22]
**Status:** 🔴 **Pending**
*   **Landed Cost Engine (LCA) (2.16, 2.21, 2.22):** Build the core financial engine that proportionally distributes all overhead expenses (freight, customs, insurance, LC charges) across imported items to determine the True Unit Cost.
*   **Vendor Returns:** Implement a return workflow for items rejected during the GRN Quality Control process.
*   **Purchase Reporting:** Generating all requested purchase reports (2.23 - 2.35).

---

> [!IMPORTANT]
> **Next Immediate Action:** We are now starting the implementation of **Step 2 (Purchase Orders & PI Details)**. I will first create a detailed Implementation Plan for the code structure of Step 2 and ask for your approval.
