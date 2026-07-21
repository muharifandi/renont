package com.nusatim.sapiriku.presentation.feature.news.list.adapter
import android.view.LayoutInflater
import android.view.ViewGroup
import androidx.core.view.isVisible
import androidx.recyclerview.widget.DiffUtil
import androidx.recyclerview.widget.RecyclerView
import coil.load
import com.nusatim.sapiriku.core.common.Config
import com.nusatim.sapiriku.core.ui.LoadingFooterListAdapter
import com.nusatim.sapiriku.core.util.ViewUtils
import com.nusatim.sapiriku.databinding.ItemListNewsBinding
import com.nusatim.sapiriku.domain.model.News
import com.nusatim.sapiriku.R

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
