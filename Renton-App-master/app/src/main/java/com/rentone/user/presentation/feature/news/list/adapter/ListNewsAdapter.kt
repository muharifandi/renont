package com.rentone.user.presentation.feature.news.list.adapter
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.view.isVisible
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.RecyclerView
import coil.load
import com.rentone.user.core.common.Config
import com.rentone.user.core.ui.LoadingFooterListAdapter
import com.rentone.user.core.util.ViewUtils
import com.rentone.user.databinding.ItemListNewsBinding
import com.rentone.user.domain.model.News
import com.rentone.user.R

class ListNewsAdapter(
    private val onItemClick: (News) -> Unit
) : LoadingFooterListAdapter<News>(DIFF_CALLBACK) {

    override fun onCreateItemViewHolder(parent: ViewGroup): RecyclerView.ViewHolder =
        NewsViewHolder(ItemListNewsBinding.inflate(LayoutInflater.from(parent.context), parent, false))

    override fun onBindItemViewHolder(holder: RecyclerView.ViewHolder, item: News, position: Int) {
        (holder as NewsViewHolder).bind(item)
    }

    inner class NewsViewHolder(private val binding: ItemListNewsBinding) : RecyclerView.ViewHolder(binding.root) {
        fun bind(item: News) {
            binding.txtDate.text = ViewUtils.mysqlDateToNormalDate(item.dateAdded.orEmpty(), "yyyy-MM-dd HH:mm:ss", "dd MMM yyyy HH:mm")
            binding.txtTitle.text = item.title

            if (item.img != null) {
                binding.imagePreview.isVisible = true
                binding.imagePreview.load(Config.BASE_NEWS_IMAGE + item.img) { error(R.drawable.no_image) }
            } else {
                binding.imagePreview.isVisible = false
            }

            binding.itemContainer.setOnClickListener { onItemClick(item) }
        }
    }

    private companion object {
        val DIFF_CALLBACK = object : DiffUtil.ItemCallback<News>() {
            override fun areItemsTheSame(oldItem: News, newItem: News) = oldItem.id == newItem.id
            override fun areContentsTheSame(oldItem: News, newItem: News) = oldItem == newItem
        }
    }
}
