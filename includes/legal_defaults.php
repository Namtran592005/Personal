<?php
// Default content for the legal pages (privacy & terms), in a light plain-text format.
// Syntax: "## " heading, "### " sub-heading, "- " list item, **bold**, [text](link).
// Placeholders {name} and {email} are replaced at render time.
// Seeded into the `pages` table on first run; editable in Admin → Pages.
return [
    'privacy' => <<<'TXT'
Chính sách này mô tả cách website {name} ("chúng tôi") thu thập, sử dụng, lưu trữ và bảo vệ dữ liệu của bạn khi bạn truy cập website và sử dụng các dịch vụ của chúng tôi. Vui lòng đọc kỹ trước khi sử dụng website.

## 1. Giới thiệu & Phạm vi áp dụng
Chính sách này áp dụng cho toàn bộ các trang và chức năng của website, bao gồm nhưng không giới hạn ở: trang chủ, khu vực liên hệ và hệ thống theo dõi truy cập. Bằng việc truy cập hoặc sử dụng website, bạn xác nhận đã đọc, hiểu và đồng ý với các nội dung nêu trong chính sách này.

## 2. Dữ liệu chúng tôi thu thập
### 2.1. Dữ liệu thu thập tự động (phân tích truy cập)
Khi bạn truy cập website, hệ thống tự động ghi nhận một số thông tin kỹ thuật phục vụ mục đích thống kê và cải thiện trải nghiệm người dùng:
- Địa chỉ IP (Internet Protocol) của thiết bị truy cập.
- User-Agent (loại trình duyệt, hệ điều hành, loại thiết bị).
- Trang giới thiệu (referrer) dẫn bạn đến website.
- Kích thước màn hình và ngôn ngữ trình duyệt.
- Trang đã truy cập và số lần truy cập.
- Thời gian xem trang (chỉ tính khi tab trình duyệt đang hiển thị, gửi về khi bạn rời trang).
- Thông tin vị trí địa lý ước tính (quốc gia, thành phố) dựa trên địa chỉ IP.
Các dữ liệu này được lưu trữ tại cơ sở dữ liệu cục bộ (SQLite) trên máy chủ lưu trữ website và không được dùng để nhận diện danh tính chính xác của bạn ngoài mục đích thống kê.

### 2.2. Dữ liệu do bạn chủ động cung cấp
Website không còn thu thập dữ liệu qua biểu mẫu liên hệ. Bạn chỉ có thể liên hệ với chúng tôi qua email hoặc mạng xã hội được hiển thị công khai trong khu vực liên hệ; việc trao đổi thông tin qua các kênh đó hoàn toàn do bạn tự quyết định.

### 2.3. Lưu trữ cục bộ trên trình duyệt (localStorage)
Website sử dụng localStorage của trình duyệt để ghi nhớ lựa chọn giao diện (chế độ sáng/tối) của bạn. Thông tin này chỉ nằm trên thiết bị của bạn, không được gửi lên máy chủ và không được chúng tôi sử dụng cho mục đích nào khác.

## 3. Mục đích xử lý dữ liệu
Chúng tôi chỉ sử dụng dữ liệu thu thập được cho các mục đích sau:
- Vận hành, bảo trì và bảo mật website.
- Thống kê số lượng truy cập, nguồn truy cập để cải thiện nội dung và trải nghiệm người dùng.
- Tuân thủ các nghĩa vụ pháp lý hiện hành.
Chúng tôi **không** sử dụng dữ liệu để xây dựng hồ sơ quảng cáo, không bán, cho thuê hay chuyển nhượng dữ liệu của bạn cho bên thứ ba vì mục đích thương mại.

## 4. Căn cứ pháp lý cho việc xử lý dữ liệu
Việc xử lý dữ liệu cá nhân của bạn dựa trên sự đồng ý của bạn khi sử dụng website. Chúng tôi cũng tuân thủ các quy định pháp luật có liên quan, bao gồm nhưng không giới hạn:
- **Việt Nam:** Nghị định số 13/2023/NĐ-CP ngày 17/04/2023 của Chính phủ về bảo vệ dữ liệu cá nhân.
- **Liên minh Châu Âu:** Quy định bảo vệ dữ liệu chung (GDPR 2016/679) nếu bạn là công dân hoặc đang sinh sống tại khu vực EU/EEA.

## 5. Chia sẻ dữ liệu với bên thứ ba
Chúng tôi không bán, cho thuê hoặc trao đổi dữ liệu cá nhân của bạn. Dữ liệu chỉ có thể được tiết lộ trong các trường hợp sau:
- **Dữ liệu công khai:** website hiển thị các kho lưu trữ (repository) công khai của tài khoản GitHub, vốn là dữ liệu công khai của GitHub.
- **Yêu cầu pháp lý:** khi có yêu cầu hợp pháp từ cơ quan nhà nước có thẩm quyền theo quy định pháp luật.

## 6. Bảo mật dữ liệu
Chúng tôi áp dụng các biện pháp kỹ thuật và tổ chức hợp lý để bảo vệ dữ liệu, bao gồm:
- Mật khẩu quản trị được mã hoá bằng thuật toán bcrypt, không lưu trữ dưới dạng văn bản thuần.
- Phiên đăng nhập (session) sử dụng cookie có thuộc tính HttpOnly và SameSite=Strict, giảm rủi ro tấn công chiếm quyền phiên và CSRF.
- Biểu mẫu đăng nhập được bảo vệ bằng token chống giả mạo (CSRF) và giới hạn số lần thử sai.
- Dữ liệu được lưu trữ tại cơ sở dữ liệu cục bộ trên máy chủ, giảm thiểu rủi ro truy cập trái phép.
- Khuyến nghị truy cập website qua kết nối HTTPS để bảo vệ dữ liệu trong quá trình truyền tải.
Dù vậy, không có phương thức truyền dữ liệu hay lưu trữ nào là tuyệt đối an toàn. Chúng tôi không thể đảm bảo bảo mật tuyệt đối.

## 7. Cookie và công nghệ tương tự
Website sử dụng cookie phiên (session) cần thiết cho việc đăng nhập khu vực quản trị và sử dụng localStorage để ghi nhớ lựa chọn giao diện. Website không sử dụng cookie của bên thứ ba, không có quảng cáo và không có công cụ theo dõi của bên ngoài. Bạn có thể xoá cookie và dữ liệu localStorage bất kỳ lúc nào thông qua cài đặt trình duyệt.

## 8. Thời gian lưu trữ dữ liệu
Dữ liệu thống kê truy cập được lưu trữ trong khoảng thời gian cần thiết cho mục đích đã nêu và có thể được xoá định kỳ thông qua khu vực quản trị.

## 9. Quyền của bạn
Tuỳ theo pháp luật áp dụng, bạn có quyền:
- Truy cập, yêu cầu sao chép dữ liệu cá nhân của mình.
- Yêu cầu chỉnh sửa hoặc cập nhật dữ liệu không chính xác.
- Yêu cầu xoá dữ liệu cá nhân khi không còn cần thiết hoặc khi thu hồi sự đồng ý.
- Hạn chế hoặc phản đối việc xử lý dữ liệu trong các trường hợp pháp luật cho phép.
- Khiếu nại tới cơ quan bảo vệ dữ liệu có thẩm quyền nếu cho rằng quyền của mình bị xâm phạm.
Để thực hiện các quyền trên, vui lòng liên hệ với chúng tôi qua thông tin tại mục 12. Chúng tôi sẽ phản hồi trong thời gian hợp lý theo quy định pháp luật.

## 10. Trẻ em
Website không hướng đến đối tượng trẻ em dưới 16 tuổi và không cố ý thu thập dữ liệu cá nhân của trẻ em. Nếu bạn là phụ huynh hoặc người giám hộ và cho rằng chúng tôi đã vô tình thu thập dữ liệu của trẻ, vui lòng liên hệ để chúng tôi xử lý và xoá dữ liệu.

## 11. Thay đổi chính sách
Chúng tôi có thể cập nhật chính sách này theo thời gian để phản ánh thay đổi về kỹ thuật, dịch vụ hoặc quy định pháp luật. Mọi thay đổi sẽ được đăng tải tại trang này kèm ngày cập nhật. Việc tiếp tục sử dụng website sau khi có thay đổi đồng nghĩa với việc bạn chấp nhận phiên bản mới của chính sách.

## 12. Liên hệ
Nếu bạn có bất kỳ câu hỏi hoặc yêu cầu nào liên quan đến chính sách quyền riêng tư này, vui lòng liên hệ:
[{email}](mailto:{email})
TXT,

    'terms' => <<<'TXT'
Các điều khoản này điều chỉnh việc bạn truy cập và sử dụng website {name} ("website"). Bằng việc truy cập website, bạn đồng ý tuân thủ toàn bộ các điều khoản nêu dưới đây. Nếu không đồng ý, vui lòng ngừng sử dụng website.

## 1. Giới thiệu & Chấp nhận điều khoản
Website là trang giới thiệu cá nhân và dịch vụ của {name}, bao gồm các mục giới thiệu, kỹ năng, dự án, bảng giá dịch vụ, câu hỏi thường gặp và liên hệ. Khi sử dụng website, bạn được coi là đã đọc, hiểu và đồng ý bị ràng buộc bởi các điều khoản này cùng với Chính Sách Quyền Riêng Tư của chúng tôi.

## 2. Sử dụng website
Bạn được phép truy cập và sử dụng website cho mục đích cá nhân, hợp pháp và không thương mại, trừ khi có thoả thuận khác bằng văn bản. Bạn đồng ý không:
- Sử dụng website cho bất kỳ mục đích bất hợp pháp hoặc vi phạm quy định pháp luật hiện hành.
- Cố gắng truy cập trái phép vào khu vực quản trị, cơ sở dữ liệu hoặc hệ thống kỹ thuật của website.
- Thực hiện các hành vi phá hoại, tấn công (bao gồm tấn công từ chối dịch vụ), quét lỗ hổng, hoặc khai thác lỗ hổng bảo mật của website.
- Thu thập dữ liệu tự động (scraping) với số lượng lớn hoặc gây ảnh hưởng đến hoạt động của website.
- Đăng tải hoặc truyền tải qua website các nội dung độc hại, phần mềm gây hại hoặc mã độc.

## 3. Quyền sở hữu trí tuệ
Toàn bộ nội dung trên website — bao gồm thiết kế, giao diện, hình ảnh, văn bản, logo, mã nguồn website và cách trình bày — là tài sản của chúng tôi hoặc của các chủ sở hữu quyền tương ứng và được bảo hộ theo quy định pháp luật về sở hữu trí tuệ. Bạn không được sao chép, tái xuất bản, phân phối, sửa đổi hoặc sử dụng lại nội dung website vì mục đích thương mại khi chưa có sự đồng ý trước bằng văn bản.
Việc sử dụng thư viện mã nguồn mở (như GSAP, Phosphor Icons, font Inter) tuân theo giấy phép của từng thư viện tương ứng.

## 4. Dự án GitHub và liên kết ngoài
Website hiển thị danh sách các kho lưu trữ (repository) công khai của tài khoản GitHub được cấu hình, thông qua API công khai của GitHub. Nội dung và giấy phép của từng kho lưu trữ do các chủ sở hữu tương ứng quy định.
Website có thể chứa liên kết đến các website bên thứ ba (GitHub, mạng xã hội, email...). Chúng tôi không kiểm soát và không chịu trách nhiệm về nội dung, chính sách quyền riêng tư hoặc hành vi của các website bên thứ ba này.

## 5. Nội dung người dùng gửi lên
Website không cung cấp biểu mẫu gửi tin nhắn nên chúng tôi không thu thập nội dung do bạn gửi trực tiếp trên website. Việc trao đổi thông tin với chúng tôi chỉ diễn ra qua các kênh liên hệ công khai (email, mạng xã hội) và thuộc trách nhiệm của bạn.

## 6. Bảng giá dịch vụ
Các mức giá, gói dịch vụ và thông tin liên quan được hiển thị trên website chỉ mang tính tham khảo và có thể thay đổi theo thời gian. Giá trị hợp đồng, phạm vi công việc và tiến độ thực hiện được thoả thuận cụ thể giữa các bên và có hiệu lực theo hợp đồng hoặc thoả thuận dịch vụ được ký kết. Thông tin hiển thị trên website không cấu thành chào bán hoặc cam kết ràng buộc cho đến khi có thoả thuận chính thức.

## 7. Giới hạn trách nhiệm
Website được cung cấp trên cơ sở "nguyên trạng" (as is) mà không có bất kỳ bảo đảm nào, dù rõ ràng hay ngụ ý, bao gồm nhưng không giới hạn bảo đảm về tính khả dụng, độ chính xác của nội dung, hoặc tính phù hợp với mục đích cụ thể. Trong phạm vi pháp luật cho phép, chúng tôi không chịu trách nhiệm đối với bất kỳ thiệt hại trực tiếp, gián tiếp, ngẫu nhiên, do hậu quả hoặc đặc biệt nào phát sinh từ việc sử dụng hoặc không thể sử dụng website, kể cả khi đã được thông báo về khả năng xảy ra các thiệt hại đó.

## 8. Luật áp dụng và giải quyết tranh chấp
Các điều khoản này được điều chỉnh và giải thích theo pháp luật của nước Cộng hoà Xã hội Chủ nghĩa Việt Nam. Mọi tranh chấp phát sinh từ hoặc liên quan đến việc sử dụng website sẽ được ưu tiên giải quyết thông qua thương lượng, hoà giải. Trong trường hợp không thể giải quyết, tranh chấp sẽ được đưa ra Toà án nhân dân có thẩm quyền tại Việt Nam để giải quyết theo quy định pháp luật.

## 9. Thay đổi điều khoản
Chúng tôi có thể sửa đổi, bổ sung các điều khoản này bất kỳ lúc nào. Phiên bản mới sẽ được đăng tải tại trang này kèm ngày cập nhật. Việc tiếp tục sử dụng website sau khi điều khoản được thay đổi đồng nghĩa với việc bạn chấp nhận các điều khoản mới. Bạn nên kiểm tra trang này định kỳ để cập nhật.

## 10. Điều khoản khác
Nếu bất kỳ điều khoản nào của thoả thuận này bị tuyên bố vô hiệu hoặc không thể thi hành, điều khoản đó sẽ được tách khỏi các điều khoản còn lại, và các điều khoản còn lại vẫn giữ nguyên hiệu lực. Việc chúng tôi không thực thi một điều khoản nào đó không cấu thành việc từ bỏ quyền được thực thi điều khoản đó.

## 11. Liên hệ
Nếu bạn có câu hỏi hoặc thắc mắc về các điều khoản này, vui lòng liên hệ qua:
[{email}](mailto:{email})
TXT,

    'privacy_en' => <<<'TXT'
This policy describes how the website {name} ("we") collects, uses, stores and protects your data when you visit the website and use our services. Please read it carefully before using the website.

## 1. Introduction & Scope
This policy applies to all pages and features of the website, including but not limited to: the homepage, the contact area and the visitor tracking system. By accessing or using the website, you confirm that you have read, understood and agreed to the contents of this policy.

## 2. Data we collect
### 2.1. Automatically collected data (visit analytics)
When you visit the website, the system automatically records some technical information for statistics and user experience improvement:
- The IP (Internet Protocol) address of the device.
- User-Agent (browser type, operating system, device type).
- The referrer page that led you to the website.
- Screen size and browser language.
- Pages visited and visit counts.
- Time spent on page (only counted while the browser tab is visible, reported when you leave).
- Estimated geographic location (country, city) based on the IP address.
This data is stored in a local database (SQLite) on the website hosting server and is not used to identify you beyond statistical purposes.

### 2.2. Data you provide voluntarily
The website no longer collects data through a contact form. You can only reach us via the email address or social media profiles listed publicly in the contact area; any exchange of information through those channels is entirely at your own discretion.

### 2.3. Browser local storage (localStorage)
The website uses browser localStorage to remember your theme preference (light/dark mode). This information stays on your device, is not sent to the server, and is not used for any other purpose.

## 3. Purpose of data processing
We only use collected data for the following purposes:
- Operating, maintaining and securing the website.
- Counting visits and traffic sources to improve content and user experience.
- Complying with applicable legal obligations.
We do **not** use the data to build advertising profiles, and we do not sell, rent or transfer your data to third parties for commercial purposes.

## 4. Legal basis for data processing
Processing of your personal data is based on your consent when using the website. We also comply with applicable laws, including but not limited to:
- **Vietnam:** Decree No. 13/2023/ND-CP dated 17/04/2023 on personal data protection.
- **European Union:** General Data Protection Regulation (GDPR 2016/679) if you are a citizen or resident of the EU/EEA.

## 5. Sharing data with third parties
We do not sell, rent or exchange your personal data. Data may only be disclosed in the following cases:
- **Public data:** the website displays public repositories of the configured GitHub account, which are public data on GitHub.
- **Legal requests:** when required by a lawful request from a competent authority.

## 6. Data security
We apply reasonable technical and organisational measures to protect data, including:
- The admin password is hashed with bcrypt and never stored in plain text.
- The login session uses cookies with HttpOnly and SameSite=Strict attributes, reducing the risk of session hijacking and CSRF.
- The login form is protected by a CSRF token and rate-limited login attempts.
- Data is stored in a local database on the server, reducing the risk of unauthorised access.
- We recommend accessing the website over an HTTPS connection to protect data in transit.
That said, no data transmission or storage method is absolutely secure. We cannot guarantee absolute security.

## 7. Cookies and similar technologies
The website uses a session cookie required for admin login and localStorage to remember your theme preference. The website does not use third-party cookies, has no ads and no external tracking tools. You can delete cookies and localStorage data at any time through your browser settings.

## 8. Data retention
Visit analytics are retained for as long as needed for the stated purposes and can be deleted periodically through the admin area.

## 9. Your rights
Depending on applicable law, you have the right to:
- Access and request a copy of your personal data.
- Request correction of inaccurate data.
- Request deletion of personal data when no longer needed or when consent is withdrawn.
- Restrict or object to data processing where the law allows.
- Lodge a complaint with the competent data protection authority if you believe your rights have been violated.
To exercise these rights, please contact us using the details in section 12. We will respond within a reasonable time as required by law.

## 10. Children
The website is not directed at children under 16 and we do not knowingly collect personal data from children. If you are a parent or guardian and believe we have inadvertently collected data from a child, please contact us so we can handle and delete it.

## 11. Policy changes
We may update this policy from time to time to reflect changes in technology, services or the law. Any changes will be posted on this page with the update date. Continued use of the website after changes means you accept the new version of the policy.

## 12. Contact
If you have any questions or requests regarding this privacy policy, please contact us:
[{email}](mailto:{email})
TXT,

    'terms_en' => <<<'TXT'
These terms govern your access to and use of the website {name} ("the website"). By accessing the website, you agree to comply with all the terms below. If you do not agree, please stop using the website.

## 1. Introduction & Acceptance of terms
The website is a personal introduction and services page for {name}, including intro, skills, projects, pricing, FAQ and contact sections. By using the website, you are deemed to have read, understood and agreed to be bound by these terms together with our Privacy Policy.

## 2. Use of the website
You may access and use the website for personal, lawful, non-commercial purposes, unless otherwise agreed in writing. You agree not to:
- Use the website for any unlawful purpose or in violation of applicable law.
- Attempt to gain unauthorised access to the admin area, database or technical systems of the website.
- Engage in destructive acts, attacks (including denial-of-service), vulnerability scanning, or exploit security flaws of the website.
- Scrape large volumes of data or interfere with the operation of the website.
- Post or transmit malicious content, harmful software or malware through the website.

## 3. Intellectual property
All content on the website — including design, interface, images, text, logos, website source code and presentation — is our property or that of the respective rights holders and is protected by intellectual property law. You may not copy, republish, distribute, modify or reuse website content for commercial purposes without prior written consent.
Use of open-source libraries (such as GSAP, Phosphor Icons, Inter font) is subject to each library's license.

## 4. GitHub projects and external links
The website displays a list of public repositories from the configured GitHub account via the public GitHub API. The content and license of each repository are determined by their respective owners.
The website may contain links to third-party websites (GitHub, social media, email...). We do not control and are not responsible for the content, privacy policies or behaviour of these third-party websites.

## 5. User-submitted content
The website does not provide a message form, so we do not collect content you submit directly on the website. Any exchange of information with us takes place only through the publicly listed contact channels (email, social media) and is entirely your responsibility.

## 6. Pricing
Prices, packages and related information displayed on the website are for reference only and may change over time. Contract value, scope of work and schedule are agreed specifically between the parties and take effect according to the signed contract or service agreement. Information displayed on the website does not constitute an offer or binding commitment until a formal agreement is reached.

## 7. Limitation of liability
The website is provided on an "as is" basis without any warranties, express or implied, including but not limited to warranties of availability, accuracy of content, or fitness for a particular purpose. To the fullest extent permitted by law, we are not liable for any direct, indirect, incidental, consequential or special damages arising from the use or inability to use the website, even if we have been advised of the possibility of such damages.

## 8. Governing law and dispute resolution
These terms are governed by and interpreted in accordance with the laws of the Socialist Republic of Vietnam. Any dispute arising from or in connection with the use of the website shall be settled first through negotiation and mediation. If resolution cannot be reached, the dispute shall be submitted to the competent People's Court in Vietnam.

## 9. Changes to terms
We may revise and supplement these terms at any time. The new version will be posted on this page with the update date. Continued use of the website after changes means you accept the new terms. You should check this page periodically for updates.

## 10. Miscellaneous
If any provision of this agreement is held to be invalid or unenforceable, that provision will be severed from the remaining provisions, and the remaining provisions remain in full force. Our failure to enforce any provision does not constitute a waiver of the right to enforce it.

## 11. Contact
If you have questions or concerns about these terms, please contact us:
[{email}](mailto:{email})
TXT,
];
