Công nghệ sử dụng
Hệ điều hành linux ubuntu
docker,laradock, composer
laravel 8
php 8.1
database : Mysql
Các package : passport , googletranslate

Các Module chức năng đã thực hiện :
Module Authenticate:
Chức năng authenticate thực hiện xác thực đăng ký:
Link api đăng ký phương thức post : localhost/api/register
Yêu cầu truyền dữ liệu lên :
email : phải nhập và phải có dạng email, chưa được sử dụng
password : phải nhập và có ít nhất 8 ký tự , xác nhận mật khẩu giống mật khẩu confirmation
name: phải nhập và nhiều nhất 150 ký tự
Dữ liệu trả về thông tin của user đã đăng ký thành công hoặc không có gì nếu thất bại
Hệ thống sẽ tạo ra 1 mã pin gồm 6 số ngẫu nhiên , và lưu thông tin user lên cơ sở dữ liệu . Rồi gửi email mã pin đến email đăng ký để thực hiện xác thực tài khoản
Chức năng đăng nhập tài khoản :
Link api đăng nhập phương thức post: localhost/api/login
Yêu cầu dữ liệu truyền lên :
email: phải nhập và phải có dạng email
password : phải nhập và có ít nhất 8 ký tự
Dữ liệu trả về nếu thành công thông tin của user đăng nhập và 1 AccessToken dùng để xác thực đã đăng nhập, nếu không thành công thông báo mật khẩu và email sai
Hệ thống sẽ kiểm tra email và mật khẩu trên cơ sở dữ liệu có chính xác hay không, nếu chính xác thì thông báo ra thông tin user và 1 accesstoken
II. Module Quản lý user
Cơ sở dữ liệu : - bảng user gồm id , name , email, avatar,email_verify_at,password,pin, status, delete_at,create_at,update_at
Liên kết vơi bảng role mối quan hệ nhiều nhiều
Bảng role liên kết với bảng permission mối quan hệ nhiều nhiều
Bảng role có id 1-4 tương ứng admin,user,editor,customer
Bảng permission có id 1-4 tương ứng view, crete, update,delete
user liên kết user_meta_mối quan hệ 1 nhiều
user lên kết với top page mối quan hệ một một
Policy : quyền create , update, view, viewany, delete, approve
Chức năng xác thực tài khoản:
Link api xác thực tài khoản phương thức post : localhost/api/verifyPin
Yêu cầu dữ liệu truyền lên :
pin: phải nhập có 6 ký tự
header: bearer access token tài khoản đã đăng nhập
Dữ liệu trả về nếu thông báo xác thực thành công hoặc xác thực thất bại hoặc đã xác thực
Hệ thống kiểm tra thời gian chỉnh sửa cuối cùng của tài khoản, nếu quá thì báo quá thời hạn và xóa mã pin đi trên cơ sở dữ liệu, nếu chưa quá 24 giờ thì sẽ kiểm tra mã pin gửi lên và mã pin được lưu trên cơ sở dữ liệu , của tài khoản đang đăng nhập có trùng khớp hay không. Nếu trùng khớp thì thông báo xác thực thành công xóa mã pin và để tài khoản trạng thái active và lưu thời gian email đã xác thực trên cơ sở dữ liệu. Trường hợp sai mã pin báo xác thực thất bại
Chức năng gửi lại mã pin cho tài khoản:
Link api gửi lại mã pin phương thức post : localhost/api/resendPin
Yêu Cầu dữ liệu truyền lên :
header: bearer access token tài khoản đã đăng nhập
Dữ liệu trả về thông báo mã pin còn hiệu lực hoặc đã xác thực hoặc gửi lại mã pin thanh công
Hệ thống kiểm tra thời gian chỉnh sửa cuối cùng nếu chưa có 24 giờ thì thông báo mã pin còn hiệu lực. Nếu tài khoản đã xác thực báo đã tài khoản đã được xác thực. Nếu không phải 2 trường hợp sau thì tạo ra 1 mã pin khác lưu vào cơ sở dữ liệu và gửi mã đó vào email tài khoản
Chức năng xem danh sách toàn bộ user:
Link api xem danh sách user phương thức get : localhost/api/user/all
Yêu cầu : đã đăng nhập và tài khoản đã xác thực và role của user đó có quyền chỉnh sửa
Có thể gửi lên :
status : là inactive hoặc active để lọc
sort_by: sắp xếp theo tên hoặc thời gian tạo hoặc thời gian sửa
sort: theo giảm dần hoặc tăng dần
query : gửi từ khóa để tìm theo tên
litmit: số bản ghi trả về
role: tìm theo tên role của user
Dữ liệu trả những bản ghi thông tin user phù hợp nếu đạt yêu cầu
Chức năng xem thông tin cá nhân:
Link api xem thông tin cá nhân phương thức get : localhost/api/user
Yêu cầu : đã đăng nhập và tài khoản đã xác thực
Dữ liệu trả về thông tin của user đang đang nhập
Chức năng tạo tài khoản :
Link api tạo tài khoản cá nhân phương thức post : localhost/api/create/user
Yêu cầu : đã đăng nhập tài khoản có quyền tạo user , chỉ có admin mới có quyền tạo tài khoản có role là admin
Dữ liệu gửi lên :
email : phải nhập, dạng email, và email chưa có trên cơ sở dữ liệu
password : phải nhập, ít nhất 8 ký tự
name : phải nhập, tối đa 150 ký tự
url_id : là 1 số
role : có thể nhập từ 1- 4 tương ứng admin, user , editor, customer
Dữ liệu trả về nếu thành công là thông tin User đã tạo
Hệ thống sẽ lưu nếu các thông tin gửi lên hợp lệ , status là active thời gian đã active là thời gian tạo và thông báo trả về dữ liệu user và 1 message
Chức năng xem thông của 1 tài khoản:
Link api xem thông tin của 1 tài khoản phương thức get ({user} là id của user trong cơ sở dữ liệu ) : localhost/api/edit/user/{user}
Yêu cầu : đã đăng nhập tài khoản có quyền sửa user
Dữ liệu trả về thông tin của 1 tài khoản
Chức năng sửa 1 tài khoản :
Link api sửa thông tin 1 tài khoản phương thức put : localhost/api/edit/user/{user}
Yêu cầu : đã đang nhập và có quyền sửa user
Dữ liệu gửi lên :
email : dạng email và chưa tồn tại trên cơ sở dữ liệu
password : nếu có thì ít nhất 8 ký tự
name : phải nhập có nhiều nhất 150 ký tự
role : phải nhập có thể là 1 số hoặc 1 mảng các số từ 1 - 4.
url_id : là 1 số
Dữ liệu trả về thông tin của user đã sửa và thông báo nếu thành công. hoặc trả lại thông báo lỗi cùng mã code response http
Hệ thống sẽ thay đổi thông tin user theo các thông tin hợp lệ được gửi lên . Trường hợp nếu role thay đổi của user là admin(1) thì user thực hiện phải có role admin nếu không sẽ báo không đủ quyền

Chức năng xóa tài khoản :
Link api xóa tài khoản phương thức delete : localhost/api/delete/user
Yêu cầu : đã đăng nhập và tài khoản có quyền xóa user
Dữ Liệu gửi lên :
ids : phải nhập, là các id của user
type : phải nhập , chỉ có thể là delete hoặc force_delete
Dữ liệu trả về 1 thông báo nếu thành công
Hệ thống sẽ xử lý nếu type là delete thì chuyển trạng thái của user thành inactive và lưu thời gian xóa là thời gian thực hiện lên cơ sở dữ liệu. Nếu force_delete thì xóa user hẳn khỏi cơ sở dữ liệu và nếu user có ảnh xóa ảnh khỏi hệ thống
Chức năng khôi phục tài khoản :
Link api khôi phục tài khoản xóa mền phương thức put : localhost/api/restore/user
Yêu cầu : đã đăng nhập và tài khoản có quyền xóa user
Dữ Liệu gửi lên :
ids : phải nhập, là các id của user
Dữ liệu trả về 1 thông báo nếu thành công
Hệ thống sẽ khôi phục những tạì khoản đã xóa mền và chuyển trạng thái về active trên cơ sở dữ liệu
Chức năng yêu thích Post :
Link api yêu thích post phương thức post : localhost/api/user/favorite
Yêu cầu : đã đăng nhập tài khoản xác thực
Dữ liệu gửi lên :
favorite: phải nhập và là 1 mảng các phần tử là id bài post
type : add hoặc là sub , phải nhập
Dữ liệu trả về : 1 message thông báo
Hệ thống xử lý nếu type là add thì thêm những id của bài post vào cơ sở dữ liệu yêu thích của từng user . Nếu type là sub thì bỏ những id của bài post ra khỏi dữ liệu yêu thích của từng user
Chức show các bài viết yêu thích :
Link api hiển thị các post yêu thích phương thức get : localhost/api/user/favorite
Yêu cầu: đã đăng nhập tài khoản xác thực
Dữ liệu trả về : nếu có bài post yêu thích thì sẽ trả về dữ liệu các bài viết đó ,nếu không thông báo 1 message thông báo không tìm thấy bài viết nào
Chức năng đổi mật khẩu cá nhân user :
Link api đổi mật khẩu cá nhân phương thức put : localhost/api/edit/my_account
Yêu cầu đã đăng nhập tài khoản xác thực
Dữ liệu gửi lên :
current_password : mật khẩu hiện tại của user
password : mật khẩu mới
password_confirmation: giống password
Dữ liệu trả về : nếu thành công thì thông báo 1 message thông báo đổi mật khẩu thành công, nếu sai mật khẩu hiện tại thông báo message mật khẩu hiện tại chưa đúng
Chức năng approve article
Link api approve article phương thức post : localhost/api/article/approve/{article}
Yêu cầu đăng nhập tài khoản xác có quyền approve
Dữ liệu gửi lên :
status :chỉ có thể là published hoặc reject, phải nhập
reson: là chuỗi (lý do từ chối)
Dữ liệu trả về 1 cái message và bài article
Chức năng approve reversion article
Link api approve article phương thức post : localhost/api/reversion/article/approve/{reversion}
Yêu cầu đăng nhập tài khoản xác có quyền approve
Dữ liệu gửi lên :
status :chỉ có thể là published hoặc reject, phải nhập
reson: là chuỗi (lý do từ chối)
Dữ liệu trả về 1 cái message và bài article
III. Module Quản lý article
Cơ sở dữ liệu : -  
article gồm id, version, title, thumbnail, new_thumbnail, user_id, article_id, description, content, category_ids, seo_content, seo_description, seo_title, slug, deleted_at, status , type, created_at, updated_at
Liên kết vơi bảng article_detail,reversion,article_meta mối quan hệ một nhiều
Liên kết với bẳng category mối quan hệ nhiều nhiều
Chức năng tạo article :
Link api chức năng tạo article phương thức post : localhost/api/create/article
Yêu cầu : đăng nhập tài khoản đã xác thực và có quyền tạo article
Dữ liệu gửi lên :
title : phải nhập
description : phải nhập
content : phải nhập
seo_content : phải nhập
seo_title : phải nhập
seo_description : phải nhập
type : phải nhập
category_ids : phải nhập , là 1 array và mỗi phần tử là số id của category
url_ids : là 1 mảng và mỗi phần tử là 1 id của ảnh đã upload lên
article_metas : dạng mảng
Dữ liệu trả về thông tin của article tên và id của category liên kết, link ảnh của article
Hệ thống thực hiện lưu thông tin lên cơ sở dữ liệu tự tạo bản dịch lưu bảng detail, nếu có article_metas thì lưu thông tin ở bảng metas với key lưu ở meta_key và value lưu ở meta_value
Chức năng hiển thị tất cả article:
Link api chức năng hiển thị tất cả phương thức get : localhost/api/article
Yêu Cầu đã đăng nhập tài khoản xác thực cơ quyền view
Dữ liệu gửi lên :
status : 'unpublished', 'published', 'draft', 'pending'
sort_by: sắp xếp theo title hoặc thời gian tạo hoặc thời gian sửa
sort: theo giảm dần hoặc tăng dần
query : gửi từ khóa để tìm theo title
litmit: số bản ghi trả về
language: hiển thị cả bản dịch
Dữ liệu trả về các article thỏa mãn điều kiện
Chức năng sửa article :
Link api chức năng tạo article phương thức post : localhost/api/edit/article/{article}
Yêu cầu : đăng nhập tài khoản đã xác thực và có quyền tạo article
Dữ liệu gửi lên :
title : phải nhập
description : phải nhập
content : phải nhập
seo_content : phải nhập
seo_title : phải nhập
seo_description : phải nhập
type : phải nhập
category_ids : phải nhập , là 1 array và mỗi phần tử là số id của category
url_ids : là 1 mảng và mỗi phần tử là 1 id của ảnh đã upload lên
article_metas : dạng mảng
Dữ liệu trả về thông tin của article tên và id của category liên kết, link ảnh của article
Hệ thống thực hiện lưu thông tin lên cơ sở dữ liệu tự tạo bản dịch lưu bảng detail, nếu có article_metas thì lưu thông tin ở bảng metas với key lưu ở meta_key và value lưu ở meta_value , xóa ảnh cũ nếu có
Chức năng xóa article :
Link api xóa article phương thức delete : localhost/api/delete/article
Yêu cầu : đã đăng nhập và tài khoản có quyền xóa user
Dữ Liệu gửi lên :
ids : phải nhập, là các id của article
type : phải nhập , chỉ có thể là delete hoặc force_delete
Dữ liệu trả về 1 thông báo nếu thành công
Hệ thống sẽ xử lý nếu type là delete thì chuyển trạng thái của article thành unpublished và lưu thời gian xóa là thời gian thực hiện lên cơ sở dữ liệu. Nếu force_delete thì xóa article hẳn khỏi cơ sở dữ liệu và nếu article có ảnh xóa ảnh khỏi hệ thống
Chức năng khôi phục article :
Link api khôi phục article xóa mền phương thức put : localhost/api/restore/article
Yêu cầu : đã đăng nhập và tài khoản có quyền xóa user
Dữ Liệu gửi lên :
ids : phải nhập, là các id của article
Dữ liệu trả về 1 thông báo nếu thành công
Hệ thống sẽ khôi phục những article đã xóa mền và chuyển trạng thái về pending trên cơ sở dữ liệu
Chức năng hiển thị 1 article chi tiết:
Link api hiển thị dữ liệu của 1 article chi tiết phương thức get : localhost/api/edit/{article}
Yêu cầu đăng nhập tài khoản đã xác thực
Dữ liệu gửi lên :
language: là 1 trong những mã sau 'ko', 'zh-CN', 'zh-TW', 'th', 'ja', 'vi'
Dữ liệu trả về thông tin của article nêu có language kem cả bản dịch tương ứng

IV. Module Upload
Chức năng up ảnh :
Link api up ảnh phương thức post : localhost/api/upload/image
Yêu cầu đăng nhập tài khoản xác thực
Dữ liệu gửi lên:
image : là 1 mảng các phần tử là ảnh tối đa 10mb
object: là 1 trong các user, article, post,category
type: avatar hoặc cover_photo
Dữ liệu trả về thông tin ảnh và link url của ảnh
Hệ thông lưu ảnh vào hệ thống và lưu thông tin ảnh vào cơ sở dữ liệu , ảnh trước khi lưu sẽ đc resize về kích thước gần nhất trong '100x2000','200x2000','300x2000','330x2000','480x2000','720x2000','1280x2000', hoặc avatar resize về 300x300 cover_photo resize về 1400x500
Chức năng up video :
Link api up video phương thức post : localhost/api/upload/video
Yêu cầu đăng nhập tài khoản xác thực
Dữ liệu gửi lên:
image : là 1 mảng các phần tử là video tối đa 10mb
object: là 1 trong các user, article, post,category
Dữ liệu trả về thông tin ảnh và link url của ảnh
Hệ thông lưu video vào hệ thống và lưu thông tin video vào cơ sở dữ liệu
V. Module Quản lý reversion article
Chức năng tạo article :
Link api chức năng tạo reversion article phương thức post : localhost/api/create/reversion/article/{article}
Yêu cầu : đăng nhập tài khoản đã xác thực và có quyền tạo reversion article
Dữ liệu gửi lên :
title : phải nhập
description : phải nhập
content : phải nhập
seo_content : phải nhập
seo_title : phải nhập
seo_description : phải nhập
type : phải nhập
category_ids : phải nhập , là 1 array và mỗi phần tử là số id của category
url_ids : là 1 mảng và mỗi phần tử là 1 id của ảnh đã upload lên
reversion_metas : dạng mảng
Dữ liệu trả về thông tin của article tên và id của category liên kết, link ảnh của article
Hệ thống thực hiện lưu thông tin lên cơ sở dữ liệu tự tạo bản dịch lưu bảng detail, nếu có article_metas thì lưu thông tin ở bảng metas với key lưu ở meta_key và value lưu ở meta_value. Reversion sẽ có version tăng thêm 1 của 1 article với mỗi bản reversion mà article đó có
Chức năng hiển thị tất cả reversion article:
Link api chức năng hiển thị tất cả phương thức get : localhost/api/reversion/article
Yêu Cầu đã đăng nhập tài khoản xác thực cơ quyền view
Dữ liệu gửi lên :
status : 'unpublished', 'published', 'draft', 'pending'
sort_by: sắp xếp theo title hoặc thời gian tạo hoặc thời gian sửa
sort: theo giảm dần hoặc tăng dần
query : gửi từ khóa để tìm theo title
litmit: số bản ghi trả về
language: hiển thị cả bản dịch
article_id: id cuar article
Dữ liệu trả về các reversion article thỏa mãn điều kiện, và đếm các bản ghi thỏa mãn
Chức năng sửa reversion article :
Link api chức năng tạo article phương thức put : localhost/api/edit/reversion/article/{reversion}
Yêu cầu : đăng nhập tài khoản đã xác thực và có quyền tạo reversion article
Dữ liệu gửi lên :
title : phải nhập
description : phải nhập
content : phải nhập
seo_content : phải nhập
seo_title : phải nhập
seo_description : phải nhập
type : phải nhập
category_ids : phải nhập , là 1 array và mỗi phần tử là số id của category
url_ids : là 1 mảng và mỗi phần tử là 1 id của ảnh đã upload lên
reversion_metas : dạng mảng
Dữ liệu trả về thông tin của reversion article tên và id của category liên kết, link ảnh của reversion article
Hệ thống thực hiện lưu thông tin lên cơ sở dữ liệu tự tạo bản dịch lưu bảng detail, nếu có reversion_metas thì lưu thông tin ở bảng metas với key lưu ở meta_key và value lưu ở meta_value , xóa ảnh cũ nếu có
Chức năng xóa reversion article :
Link api xóa reversion article phương thức delete : localhost/api/delete/reversion/article
Yêu cầu : đã đăng nhập và tài khoản có quyền xóa user
Dữ Liệu gửi lên :
ids : phải nhập, là các id của reversion article
type : phải nhập , chỉ có thể là delete hoặc force_delete
Dữ liệu trả về 1 thông báo nếu thành công
Hệ thống sẽ xử lý nếu type là delete thì chuyển trạng thái của article thành unpublished và lưu thời gian xóa là thời gian thực hiện lên cơ sở dữ liệu. Nếu force_delete thì xóa reversion article hẳn khỏi cơ sở dữ liệu và nếu reversion article có ảnh xóa ảnh khỏi hệ thống
Chức năng khôi phục reversion article :
Link api khôi phục article xóa mền phương thức put : localhost/api/restore/reversion/article
Yêu cầu : đã đăng nhập và tài khoản có quyền xóa user
Dữ Liệu gửi lên :
ids : phải nhập, là các id của article
Dữ liệu trả về 1 thông báo nếu thành công
Hệ thống sẽ khôi phục những article đã xóa mền và chuyển trạng thái về pending trên cơ sở dữ liệu
Chức năng hiển thị 1 reversion article chi tiết:
Link api hiển thị dữ liệu của 1 article chi tiết phương thức get : localhost/api/edit/reversion/article/{reversion}
Yêu cầu đăng nhập tài khoản đã xác thực
Dữ liệu gửi lên :
language: là 1 trong những mã sau 'ko', 'zh-CN', 'zh-TW', 'th', 'ja', 'vi'
Dữ liệu trả về thông tin của article nêu có language kem cả bản dịch tương ứng
VI. Module Quản lý Post
Chức năng tạo post :
Link api chức năng tạo post phương thức post : localhost/api/create/post
Yêu cầu : đăng nhập tài khoản đã xác thực và có quyền tạo post
Dữ liệu gửi lên :
title : phải nhập
description : phải nhập
content : phải nhập
type : phải nhập
category_ids : phải nhập , là 1 array và mỗi phần tử là số id của category
url_ids : là 1 mảng và mỗi phần tử là 1 id của ảnh đã upload lên
post_metas : dạng mảng
Dữ liệu trả về thông tin của post tên và id của category liên kết, link ảnh của post
Hệ thống thực hiện lưu thông tin lên cơ sở dữ liệu tự tạo bản dịch lưu bảng detail, nếu có post_metas thì lưu thông tin ở bảng metas với key lưu ở meta_key và value lưu ở meta_value
Chức năng hiển thị tất cả post:
Link api chức năng hiển thị tất cả phương thức get : localhost/api/post
Yêu Cầu đã đăng nhập tài khoản xác thực cơ quyền view
Dữ liệu gửi lên :
status : active , inactive
sort_by: sắp xếp theo title hoặc thời gian tạo hoặc thời gian sửa
sort: theo giảm dần hoặc tăng dần
query : gửi từ khóa để tìm theo title
litmit: số bản ghi trả về
language: hiển thị cả bản dịch
Dữ liệu trả về các post thỏa mãn điều kiện
Chức năng sửa post :
Link api chức năng tạo post phương thức post : localhost/api/edit/post/{post}
Yêu cầu : đăng nhập tài khoản đã xác thực và có quyền tạo post
Dữ liệu gửi lên :
title : phải nhập
description : phải nhập
content : phải nhập
type : phải nhập
category_ids : phải nhập , là 1 array và mỗi phần tử là số id của category
url_ids : là 1 mảng và mỗi phần tử là 1 id của ảnh đã upload lên
post_metas : dạng mảng
Dữ liệu trả về thông tin của article tên và id của category liên kết, link ảnh của article
Hệ thống thực hiện lưu thông tin lên cơ sở dữ liệu tự tạo bản dịch lưu bảng detail, nếu có post_metas thì lưu thông tin ở bảng metas với key lưu ở meta_key và value lưu ở meta_value , xóa ảnh cũ nếu có
Chức năng xóa article :
Link api xóa article phương thức delete : localhost/api/delete/post
Yêu cầu : đã đăng nhập và tài khoản có quyền xóa user
Dữ Liệu gửi lên :
ids : phải nhập, là các id của article
type : phải nhập , chỉ có thể là delete hoặc force_delete
Dữ liệu trả về 1 thông báo nếu thành công
Hệ thống sẽ xử lý nếu type là delete thì chuyển trạng thái của post thành inactive và lưu thời gian xóa là thời gian thực hiện lên cơ sở dữ liệu. Nếu force_delete thì xóa article hẳn khỏi cơ sở dữ liệu và nếu article có ảnh xóa ảnh khỏi hệ thống
Chức năng khôi phục post :
Link api khôi phục article xóa mền phương thức put : localhost/api/restore/post
Yêu cầu : đã đăng nhập và tài khoản có quyền xóa user
Dữ Liệu gửi lên :
ids : phải nhập, là các id của post
Dữ liệu trả về 1 thông báo nếu thành công
Hệ thống sẽ khôi phục những post đã xóa mền và chuyển trạng thái về pending trên cơ sở dữ liệu
Chức năng hiển thị 1 post chi tiết:
Link api hiển thị dữ liệu của 1 post chi tiết phương thức get : localhost/api/edit/{post}
Yêu cầu đăng nhập tài khoản đã xác thực
Dữ liệu gửi lên :
language: là 1 trong những mã sau 'ko', 'zh-CN', 'zh-TW', 'th', 'ja', 'vi'
Dữ liệu trả về thông tin của post nêu có language kem cả bản dịch tương ứng
VII. Module Quản lý category
Chức năng tạo category :
Link api chức năng tạo category phương thức category : localhost/api/create/category
Yêu cầu : đăng nhập tài khoản đã xác thực và có quyền tạo category
Dữ liệu gửi lên :
title : phải nhập
description : phải nhập
content : phải nhập
type : phải nhập
post_ids : là 1 array và mỗi phần tử là số id của post
url_ids : là 1 mảng và mỗi phần tử là 1 id của ảnh đã upload lên
Dữ liệu trả về thông tin của category tên và id của category liên kết, link ảnh của category
Hệ thống thực hiện lưu thông tin lên cơ sở dữ liệu
Chức năng hiển thị tất cả category:
Link api chức năng hiển thị tất cả phương thức get : localhost/api/category
Yêu Cầu đã đăng nhập tài khoản xác thực cơ quyền view
Dữ liệu gửi lên :
status : active, inactive
sort_by: sắp xếp theo title hoặc thời gian tạo hoặc thời gian sửa
sort: theo giảm dần hoặc tăng dần
query : gửi từ khóa để tìm theo title
litmit: số bản ghi trả về
language: hiển thị cả bản dịch
Dữ liệu trả về các active thỏa mãn điều kiện
Chức năng sửa category :
Link api chức năng tạo post phương thức post : localhost/api/edit/category/{category}
Yêu cầu : đăng nhập tài khoản đã xác thực và có quyền tạo post
Dữ liệu gửi lên :
title : phải nhập
description : phải nhập
content : phải nhập
type : phải nhập
post_ids : là 1 array và mỗi phần tử là số id của category
url_ids : là 1 mảng và mỗi phần tử là 1 id của ảnh đã upload lên
Dữ liệu trả về thông tin của category tên và id của category liên kết, link ảnh của category
Hệ thống thực hiện lưu thông tin lên cơ sở dữ liệu
Chức năng xóa category :
Link api xóa category phương thức delete : localhost/api/delete/category
Yêu cầu : đã đăng nhập và tài khoản có quyền xóa category
Dữ Liệu gửi lên :
ids : phải nhập, là các id của category
type : phải nhập , chỉ có thể là delete hoặc force_delete
Dữ liệu trả về 1 thông báo nếu thành công
Hệ thống sẽ xử lý nếu type là delete thì chuyển trạng thái của category thành inactive và lưu thời gian xóa là thời gian thực hiện lên cơ sở dữ liệu. Nếu force_delete thì xóa article hẳn khỏi cơ sở dữ liệu và nếu category có ảnh xóa ảnh khỏi hệ thống
Chức năng khôi phục category :
Link api khôi phục category xóa mền phương thức put : localhost/api/restore/category
Yêu cầu : đã đăng nhập và tài khoản có quyền xóa user
Dữ Liệu gửi lên :
ids : phải nhập, là các id của category
Dữ liệu trả về 1 thông báo nếu thành công
Hệ thống sẽ khôi phục những category đã xóa mền và chuyển trạng thái về active trên cơ sở dữ liệu
Chức năng hiển thị 1 post chi tiết:
Link api hiển thị dữ liệu của 1 post chi tiết phương thức get : localhost/api/edit/category/{category}
Yêu cầu đăng nhập tài khoản đã xác thực
Dữ liệu trả về thông tin của post
VIII. Module Top Page
Chức năng tạo top page
Link api tạo top page phương thức post : localhost/api/create/toppage
Yêu câu đăng nhập tài khoản xác thực
Dữ liệu gửi lên:
area : phải nhập, có dạng string/string
about : phải nhập là string có tối đa 200 ký tự
summary : phải nhập là string có tối đa 1000 ký tự
name : phải nhập
facebook : link cá nhân của facebook
instagram: link cá nhân của instagram
website: link website
status: phải nhập published hoặc unpublished
video : id của video
avatar: id của ảnh avatar
cover_photo : id ảnh bìa top page
Dữ liệu trả về thông tin top page và 1 mesage thông báo thành công
Chức năng sửa top page
Link api tạo top page phương thức put : localhost/api/edit/toppage/{user}
Yêu câu đăng nhập tài khoản xác thực
Dữ liệu gửi lên:
area : phải nhập, có dạng string/string
about : phải nhập là string có tối đa 200 ký tự
summary : phải nhập là string có tối đa 1000 ký tự
name : phải nhập
facebook : link cá nhân của facebook
instagram: link cá nhân của instagram
website: link website
status: phải nhập published hoặc unpublished
video : id của video
avatar: id của ảnh avatar
cover_photo : id ảnh bìa top page
Dữ liệu trả về thông tin top page và 1 mesage thông báo thành công
Chức năng xem toppage của 1 user :
Link api xem top page của 1 user phương thức get : localhost/api/edit/toppage/{user}
Yêu cầu tài khoản đã xác thực
Dữ liệu trả về thông tin của toppage user đó
Chức năng thay đổi trạng thái top page:
Link api thay đổi trạng thái top page phương thưc put : localhost/api/change/toppage/status
Yêu cầu tài khoản đã xác thực
Dữ liệu gửi lên :
status : phải nhập , là published hoặc unpublished
dữ liệu trả về top page
Chức năng thay đổi bản dịch ngôn ngữ :
Link api thay đổi bản dịch top page phương thưc put : localhost/api/update/toppage/detail
Yêu cầu tài khoản đã xác thực
Dữ liệu gửi lên :
area : phải nhập, có dạng string/string
about : phải nhập là string có tối đa 200 ký tự
summary : phải nhập là string có tối đa 1000 ký tự
name : phải nhập
language : là 1 trong những mã sau 'ko', 'zh-CN', 'zh-TW', 'th', 'ja', 'vi'

dữ liệu trả về bản dịch đã được thay đổi
Một số hàm khác :
Helper translate, checkused (check các file sử dụng nếu ko xóa đi), deletefile(xóa file cũ đi)
Chức năng phân quyền (policy)
controller response trả về dạng json
seeder tạo dữ liệu mẫu cho cơ sở dữ liệu
