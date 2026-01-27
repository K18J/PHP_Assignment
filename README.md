# Insurance Policy Management System

Object-oriented PHP application for managing multiple types of insurance policies with premium calculation, risk assessment, discounts, endorsements, and pro-rata cancellations.

## 🎯 Features

### Core Functionality
- **Multi-Policy Support**: Vehicle, Health, Life, and Property insurance policies
- **Premium Calculation**: Sophisticated algorithm considering base rates, risk factors, and discounts
- **Risk Assessment**: Customizable risk scoring with multiplier-based adjustments
- **Discount System**: Loyalty, no-claim, bundle, and VIP discounts (up to 25% total)
- **Policy Endorsements**: Mid-term policy modifications with automatic premium adjustments
- **Pro-Rata Cancellation**: Fair refund calculation based on unused policy duration
- **Status Management**: State machine implementation for policy lifecycle (Draft → Pending → Active → Expired/Cancelled)
- **Underwriting Service**: Automated policy validation and approval workflow

### Design Patterns & Principles
- **Strategy Pattern**: Interchangeable premium calculators per policy type
- **Template Method**: Abstract policy with type-specific implementations
- **Value Objects**: Immutable Money, PolicyStatus, RiskAssessment, CoverageType
- **State Pattern**: Policy status transitions with validation
- **Service Layer**: Separation of business logic (UnderwritingService, PolicyIssuanceService)

<img width="1653" height="969" alt="image" src="https://github.com/user-attachments/assets/d21d2be6-5276-48a2-8f92-5e6085a03a4f" />

## 🚀 Getting Started

### Requirements
- PHP 8.0 or higher
- No external dependencies required

### Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd Insurance_Policy_Management_System

2. Run the demo:
php -S localhost:8000 -t demo

3. Open your browser and navigate to:
http://localhost:3000/demo/policy_demo.php
