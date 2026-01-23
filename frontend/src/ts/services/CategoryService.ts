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
    private cached: ResponseResult<Category[]> | null = null;

    constructor(api: ApiService) {
        this.repo = new Repository(api, 'categories');
    }

    public index = async (): Promise<ResponseResult<Category[]>> => {
        if (this.cached) {
            return this.cached;
        } else {
            const response = await this.repo.index();

            if (response.success && response.data) {
                this.cached = response;
            }
            
            return response;
        }
    }

    public async preparedIndex(): Promise<ResponseResult<GroupedCategories>> {
        const raw = this.cached ?? await this.index();
        
        if (!raw.success || !raw.data) {
            return {
                success: false,
                status: raw.status,
                message: raw.message,
            };
        }

        try {
            const grouped = Object.groupBy(raw.data, category => 
                category.parent?.id.toString() ?? 'parent'
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
}