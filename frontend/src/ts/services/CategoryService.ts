import type ApiService from "@/ts/services/ApiService";
import Repository from "@/ts/services/Repository";
import type { Category, GroupedCategories } from "@/ts/entities/Category";
import type ResponseResult from "@/ts/types/ResponseResult";
/**
 * Category management service
 */
export default class CategoryService
{
    private repo: Repository;

    constructor(api: ApiService) {
        this.repo = new Repository(api, 'categories');
    }

    public index = async (): Promise<ResponseResult<Category[]>> => await this.repo.index()

    public async preparedIndex(): Promise<ResponseResult<GroupedCategories>> {
        const raw = await this.index();
        
        if (!raw.success || !raw.data) {
            return {
                success: false,
                status: raw.status,
                message: raw.message,
            };
        }

        try {
            const grouped = Object.groupBy(raw.data, category => 
                category.parent_id?.toString() ?? 'parent'
            );

            const result: GroupedCategories = {};

            const parentCategories = grouped.parent || [];
            parentCategories.forEach(parent => {
                result[parent.name] = grouped[parent.id] || [];
            });

            return {
                success: true,
                status: raw.status,
                message: 'Categories grouped successfully',
                data: result
            };

        } catch (error) {
            return {
                success: false,
                status: 500,
                message: 'Failed to group categories'
            };
        }
    }

    public loadOptions = async (): Promise<ResponseResult> => {
        const response = await this.repo.file(`${window.location.origin}/assets/json/tags.json`, 'json');
        return {
            ...response,
            data: response.data.tags
        }
    }
}