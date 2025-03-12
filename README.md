# openQRM - Open Source Data Center Management and Cloud Computing Platform
**openQRM** is an open-source, enterprise-grade data center management and cloud computing platform. It simplifies orchestration, automation, monitoring, and deployment of cloud services, virtual servers, containers, and bare-metal resources, greatly enhancing the way your IT infrastructure and cloud resources are managed.

# openQRM is released as a Community and qn Enterprise version. 
This repository is for the Community version. The Enterprise version includes Commercial plugins and eligible for support and deployment assistance. Both versions are 100% open source. [More information about the Enterprise version.](https://openqrm-enterprise.com/enterprise-edition/)
## 🌟 Features
- **Cloud Orchestration**: Manage virtualized and physical resources centrally.
- **Unified Management**: Support for resources like KVM, VMware, Xen, Hyper-V, LXC Containers, and Docker.
- **Deployment Automation**: Reliable and automated server deployment and lifecycle management.
- **Monitoring & Alerts**: Built-in monitoring system with proactive alerts.
- **Flexible API Management**: SOAP and RESTful API makes integration easy.
- **High Availability**: Designed to provide maximum uptime and reliability.
- **Storage Management**: Comprehensive support for storage integration including SAN, NAS, CEPH, NFS, iSCSI, and more.
- **Scalable & Extensible**: Highly scalable architecture that grows with your business.
- **Memory Resident Operating Systems**: [Convert platforms like Proxmox to a memory resident operating system](https://wiki.openqrm-enterprise.com/index.php?title=How_to_build_Proxmox_tmpfs_image).

## **Memory-Resident Operating System (RAM-based OS)**
A **Memory-Resident Operating System** is an operating system architecture or configuration designed to run entirely within RAM (Random Access Memory). Instead of relying on persistent storage such as hard drives or SSDs, the OS is loaded into memory at initialization. As a result, the entire system state is ephemeral and does not persist through reboots.

### **Key Characteristics:**
- **High Performance:** Due to operations running entirely in RAM, it's extremely fast for read/write tasks.
- **Ephemeral State:** Any changes made during runtime are lost upon shutdown/reboot unless saved externally.
- **Enhanced Security:** Smaller attack surface and fewer persistent entry points for malware and security breaches.
- **Config Syncing:** Solutions available for configuration synchronisations. Maintain state and configuration initialisation during reboots.

## **TMPFS-based Operating System**
A **TMPFS-based Operating System** leverages an in-memory filesystem (`TMPFS`) as its primary file storage medium. tmpfs resides entirely in virtual memory (backed by RAM and swap if necessary), providing high-speed access to system resources with dynamic allocation.

### **Key Characteristics:**
- **Dynamic Capacity:** Expands or shrinks based on application usage, bounded by available memory.
- **Temporary Storage:** Data is stored purely in memory and does not persist across reboots, offering complete volatility.
- **Efficient Performance:** Filesystem operations (read/write) are significantly faster due to RAM-based storage.

## **Common Use-Cases:**
- Embedded Systems with limited physical writable storage.
- Live or rescue operating systems loaded from external media (like USB drives or PXE boot).
- Secure environments or volatile virtual servers where persistence is not required or preferred.
- Containerized and isolated applications where temporary storage is sufficient.

In short, describing these operating systems emphasizes their non-persistent nature, speed advantages, security considerations, and dynamic memory usage characteristics.

The **openQRM** platform supports multiple deployment types, enabling flexibility and compatibility with diverse infrastructure scenarios. The main deployment types supported by **openQRM** include:

## 🚀 openQRM Deployment Types

### 1. **Bare-Metal Deployment**
Provision and manage physical hardware servers directly.
- **Advantages:**
    - High performance and resource utilization
    - Direct, hardware-level control
    - Ideal for resource-intensive workloads

- **Typical Use-cases:**
    - Data-intensive applications
    - High-performance computing (HPC)
    - Infrastructure services requiring maximum performance

### 2. **Virtual Machine Deployment**
OpenQRM supports multiple virtualization technologies, such as KVM, VMware, Xen, and Hyper-V.
- **Advantages:**
    - Rapid resource provisioning and flexibility
    - Efficient utilization of hardware resources via virtualization
    - Isolation and ease of management

- **Typical Use-cases:**
    - Multi-tenant cloud environments
    - Development and testing environments
    - Production workloads requiring scalability

### 3. **Cloud & Public/Private Hybrid Deployment**
Deploy and manage resources across hybrid cloud environments, including public cloud providers, private clouds, or hybrid setups.
- **Advantages:**
    - Flexibility and elasticity
    - Optimal resource management between local and cloud-hosted resources
    - Support for scaling from local infrastructure to public cloud seamlessly

- **Typical Use-cases:**
    - Hybrid cloud deployments for elastic scaling
    - Failover and high-availability infrastructure
    - Workload migration and disaster recovery scenarios

### 4. **Container Deployment**
OpenQRM supports LXC containers and Docker management, allowing streamlined container-based application deployment and orchestration.
- **Advantages:**
    - Lightweight and rapid application provisioning
    - Ideal for microservices architecture
    - Efficient resource usage and deployment time

- **Typical Use-cases:**
    - Continuous integration/Continuous deployment (CI/CD)
    - Microservices-based architectures
    - Rapid scaling and application lifecycle management

### 5. **Storage Deployment**
Manage and integrate storage technologies like SAN, NAS, NFS, CEPH, iSCSI, and clustered file systems.
- **Advantages:**
    - Unified storage management
    - Integration with various storage backends
    - High availability and redundancy

- **Typical Use-cases:**
    - Data storage and backups
    - Virtual infrastructure backing storage
    - Support for high availability clusters and shared storage systems

### 6. **High Availability Deployment**
Provide redundancy and clustering capabilities to ensure high availability (HA).
- **Advantages:**
    - Minimize downtime and increase reliability
    - Load balancing and failure protection mechanisms
    - Automatic recovery and self-healing capabilities

- **Typical Use-cases:**
    - Critical production environments
    - Database clusters and web application backends
    - Enterprise-grade SLA fulfillment

Each deployment type has specific strengths, enabling openQRM users to select the best-suited method to fulfill their infrastructure requirements.

The openQRM platform significantly extends its functionality and capabilities through a versatile system of **plugins**. These plugins provide modular enhancements that empower system administrators to extend openQRM's core features according to specific deployment scenarios and user requirements.

Here’s a clear overview and description of the main categories of **openQRM plugins**, their typical users, and common applications:

---

## 🧩 openQRM Plugin Categories and Their Use-Cases

### 1. **Virtualization Plugins**
These plugins extend the capability of virtual infrastructure management within openQRM. They integrate popular virtualization solutions.

- **Examples:**  
  - KVM  
  - VMware vSphere/ESXi  
  - Xen  
  - Hyper-V  

- **Target Users:**  
  - System administrators   
  - Cloud operators  
  - IT infrastructure engineers  

- **Applications and Use-cases:**  
  - Virtualized resource provisioning and lifecycle management  
  - Multi-tenant and private cloud deployments  
  - Data-center consolidation and server virtualization  

---

### 2. **Container & Microservices Plugins**
Container-related plugins extend support for running containerized applications and services.

- **Examples:**  
  - Docker  
  - LXC  

- **Target Users:**  
  - DevOps teams  
  - Application developers  
  - Cloud-native architects  

- **Applications and Use-cases:**  
  - Container orchestration and rapid app deployment  
  - Microservices architectures  
  - Continuous integration/deployment pipelines (CI/CD)  

---

### 3. **Storage Plugins**
Storage plugins integrate personalized storage solutions directly into openQRM, enabling seamless management of various storage backends.

- **Examples:**  
  - SAN (Storage Area Network): iSCSI, Fibre Channel  
  - NAS solutions  
  - NFS (Network File System)  
  - GlusterFS, CEPH  

- **Target Users:**  
  - Storage administrators  
  - Infrastructure specialists  

- **Applications and Use-cases:**  
  - Consolidated storage management  
  - Backups and data integrity assurance  
  - Shared and distributed storage for virtualized environments  

---

### 4. **Network Plugins**
Enables advanced network management, load balancing, dynamic IP allocation, and DNS integration within openQRM.

- **Examples:**  
  - DNS/DHCP Integration  
  - IP Management (IPMI support)  
  - Network Isolation and VLAN support  

- **Target Users:**  
  - Network administrators  
  - System architects  

- **Applications and Use-cases:**  
  - Network automation in data centers  
  - Dynamic network configurations for cloud deployments  
  - Robust IP management in multitenant environments  

---

### 5. **Monitoring & High Availability Plugins**
Extends monitoring capabilities and high-availability management of infrastructure resources.

- **Examples:**  
  - Nagios plugins (integrated monitoring)  
  - Zabbix integration  
  - High Availability clustering  

- **Target Users:**  
  - System administrators  
  - Cloud platform engineers  

- **Applications and Use-cases:**  
  - Proactive monitoring and alerting for infrastructure  
  - Automatic recovery and failover solutions  
  - SLA-driven infrastructure management  

---

### 6. **Cloud & Hybrid Integration Plugins**
These plugins enable deeper integration with public or hybrid cloud deployments, allowing resources to bridge multiple cloud providers seamlessly.

- **Examples:**  
  - AWS EC2 management  
  - Azure Integration  
  - OpenStack Integration  

- **Target Users:**  
  - Cloud architects  
  - Hybrid-cloud infrastructure teams  

- **Applications and Use-cases:**  
  - Management of hybrid-cloud deployments  
  - Efficient resource allocation and control  
  - Cost-optimized elastic scalability  

---

### 7. **Deployment and Provisioning Plugins**
Support various provisioning methods including automated deployment, operating system templates, and image management.

- **Examples:**  
  - PXE Boot plugins  
  - ISO Deployment  
  - Automated Server Deployment workflows  

- **Target Users:**  
  - System deployment teams  
  - IT automation specialists  

- **Applications and Use-cases:**  
  - Rapid server and OS deployments  
  - Automated disaster recovery processes  
  - Dynamic provisioning environments  

---

### 8. **Authentication & Security Plugins**
Extend access control, authentication, and secure management capabilities.

- **Examples:**  
  - LDAP integration  
  - Active Directory authentication  
  - User role and permission management  

- **Target Users:**  
  - Security administrators  
  - Compliance teams  

- **Applications and Use-cases:**  
  - Centralized authentication and authorization  
  - Granular security policy management  
  - Compliance and audit support  

---

### 9. **API and Automation Plugins**
Allows integration and automation through open standard APIs (SOAP & REST APIs).

- **Examples:**  
  - SOAP & REST API Integration  
  - Remote management and orchestration APIs  

- **Target Users:**  
  - DevOps Automation engineers  
  - Developers  

- **Applications and Use-cases:**  
  - Infrastructure-as-Code automation  
  - Custom integration with external systems (monitoring, CI/CD)  
  - Automated workflows and infrastructure updates  

---

Each plugin provides modular, extendable capabilities, allowing users to tailor their openQRM installations specifically to their operational needs. Users can selectively enable plugins according to their use-case and scale.

Feel free to ask if there is anything more specific you’d like to know about openQRM plugins!
###


## 🛠️ Installation
### Requirements
- Debian/Ubuntu (Recommended) or CentOS/RHEL Linux
- Apache Web Server
- MySQL/MariaDB Database
- PHP 7.x/8.x
- Basic Networking Knowledge

### Quick Installation Steps
Clone repository or download the latest stable release:
``` bash
git clone https://github.com/openQRM/openqrm.git
```
or
``` bash
wget https://github.com/openQRM/openqrm-community/releases/download/v5.3.60/openQRM-5.3.60-Community-Edition.tgz
cd openqrm
```
Run the installer script:
``` bash
chmod +x install-openqrm.sh
sudo ./install-openqrm.sh
```
Open your browser and navigate to:
``` 
http://your-server-ip/openqrm
```

## 📚 Documentation
For complete installation instructions, GUI manual, advanced usage examples, RESTful API guides, and comprehensive documentation, visit the official documentation:
- [openQRM Wiki](https://wiki.openqrm-enterprise.com/view/Main_Page)
- [openQRM User Guide] (https://me2.us/openQRM-User-Guide)
- [openQRM Admin Guide] (https://me2.us/openQRM-Admin-Guide)

## 🛎️ Community Support
- Have questions? Need some help? Reach out to our supportive community!
- Open an issue or suggest improvements directly on GitHub.
- Join the community forums.

## 💡 Contributing
Contributions and pull requests are very welcomed and greatly appreciated. To contribute to openQRM, follow these steps:
1. Fork the repository.
2. Create your Feature Branch (`git checkout -b feature/YourFeature`)
3. Commit your changes (`git commit -m 'Add YourFeature'`)
4. Push to your branch (`git push origin feature/YourFeature`)
5. Open a Pull Request.

## 🧾 License
This openQRM Community version is released under the [GNU General Public License v2](LICENSE). Please see the `LICENSE` file for more information.

## 📅 Project Status
openQRM remains actively developed and supported through its active community and contributors.
🌐 **[Visit openQRM Official Website](https://openqrm-enterprise.com)**
**Thank you for using openQRM!**


## 🛡️ Security Bug Reporting Process
We highly value the security of our system and rely on accurate reporting from our community and users. If you discover or suspect a security vulnerability, please follow this process carefully:
1. **Prepare a Detailed Report:**
Clearly document the vulnerability, affected components, and the steps needed to reproduce the issue.
2. **Classify Severity:**
Indicate the perceived security risk clearly (Critical / High / Medium / Low).
3. **Proof of Concept (PoC):**
Include supporting information such as code snippets, screenshots, or examples that illustrate the exploit or observed vulnerability.
4. **Suggested Resolution:**
Provide recommendations or insights regarding potential fixes or mitigations, if available.
5. **Secure Reporting:**
Submit your security vulnerability reports securely via email to `[help@openqrm-enterprise.com.au]`. Please avoid submitting sensitive information through public channels or issue trackers.

Our security team analyzes reported vulnerabilities, confirms their validity, and promptly communicates findings with the reporter. We ensure confidentiality and proper handling of every submitted issue.
Thank you for helping us keep our systems secure!

